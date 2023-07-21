<?php

namespace Onetree\SetupTheme\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

class Page
{
    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    private $fixtureManager;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $pageFactory;
    /**
     * @var \Magento\Cms\Model\ResourceModel\Page
     */
    private $pageResourceModel;
    /**
     * @var \Magento\Cms\Model\PageRepository
     */
    private $pageRepository;
    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    private $storeFactory;
    /**
     * @var \Magento\Store\Model\ResourceModel\Store
     */
    private $storeResourceModel;
    /**
     * @var \Magento\Store\Model\StoreRepository
     */
    private $storeRepository;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $io;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Theme\Model\ThemeFactory
     */
    private $themeFactory;
    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme
     */
    private $themeResourceModel;
    /**
     * @var string
     */
    private $moduleName;

    /**
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Cms\Model\ResourceModel\Page $pageResourceModel
     * @param \Magento\Cms\Model\PageRepository $pageRepository
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Store\Model\ResourceModel\Store $storeResourceModel
     * @param \Magento\Store\Model\StoreRepository $storeRepository
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Filesystem\Io\File $io
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Theme\Model\ThemeFactory $themeFactory
     * @param \Magento\Theme\Model\ResourceModel\Theme $themeResourceModel
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Model\ResourceModel\Page $pageResourceModel,
        \Magento\Cms\Model\PageRepository $pageRepository,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Store\Model\ResourceModel\Store $storeResourceModel,
        \Magento\Store\Model\StoreRepository $storeRepository,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Filesystem\Io\File $io,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Theme\Model\ThemeFactory $themeFactory,
        \Magento\Theme\Model\ResourceModel\Theme $themeResourceModel
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->pageFactory = $pageFactory;
        $this->pageResourceModel = $pageResourceModel;
        $this->pageRepository = $pageRepository;
        $this->storeFactory = $storeFactory;
        $this->storeResourceModel = $storeResourceModel;
        $this->storeRepository = $storeRepository;
        $this->objectManager = $objectManager;
        $this->io = $io;
        $this->logger = $logger;
        $this->themeFactory = $themeFactory;
        $this->themeResourceModel = $themeResourceModel;
    }

    /**
     * @param array $fixtures
     * @throws \Exception
     */
    public function install(array $fixtures)
    {
        foreach ($fixtures as $fileName) {
            list($moduleName, $filePath) = \Magento\Framework\View\Asset\Repository::extractModule(
                $this->fixtureManager->normalizePath($fileName)
            );
            $this->moduleName = $moduleName;

            $fileName = $this->fixtureManager->getFixture($fileName);
            if (!file_exists($fileName)) {
                continue;
            }

            $rows = $this->csvReader->getData($fileName);
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $row = $data;

                $page = $this->pageFactory->create();
                // load page if exists
                $this->pageResourceModel->load($page, $row['identifier'], 'identifier');
                // parse {{widget_slider_Homepage_V10}} to load slider inside content
                //$this->parseSliderSnippet($row);
                //
                //$this->parseWidgetBannerSnippet($row);
                // parse custom theme,
                $this->parseCustomTheme($row);
                // copy image if exists medi url, ex {{media url="wysiwyg/test/testimage.jpg"}}
                $this->uploadImageToWysiwygFolder($row);
                $page->addData($row);

                // get stores to show the page according store
                $stores = $this->getStores($row);
                $page->setStores($stores);
                $this->pageRepository->save($page);
            }
        }
    }

    /**
     * @param $row
     * @return array
     */
    private function getStores($row)
    {
        $stores = [];
        if (isset($row['storeview_code']) && !empty($row['storeview_code'])) {
            $scopeCode = explode(',', $row['storeview_code']);
            if ($scopeCode) {
                foreach ($scopeCode as $storeCode) {
                    $storeView = $this->storeFactory->create();
                    $this->storeResourceModel->load($storeView, trim($storeCode), 'code');
                    if ($storeView->getId()) {
                        $stores[] = $storeView->getId();
                    }
                }
            }
        }

        if (empty($stores)) {
            $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID];
        }

        return $stores;
    }

    /**
     * @param $content
     */
    private function parseSliderSnippet(&$row)
    {
        $content = $row['content'];
        if (preg_match_all('/{{widget_slider_[^}}]*}}/i', $content, $result)) {
            $result = (isset($result[0])) ? $result[0] : [];
            foreach ($result as $widget) {
                $sliderId = $this->getSliderIdByTitle($widget);
                $widgetContent = '{{widget type="WeltPixel\OwlCarouselSlider\Block\Slider\Custom" slider_id="'. $sliderId .'"}}';
                $row['content'] = str_replace($widget, $widgetContent, $row['content']);
            }
        }
    }

    /**
     * @param $content
     */
    private function parseWidgetBannerSnippet(&$row)
    {
        $content = $row['content'];
        if (preg_match_all('/{{widget_banner_[^}}]*}}/i', $content, $result)) {
            $result = (isset($result[0])) ? $result[0] : [];
            foreach ($result as $widget) {
                $widgetBanner = $this->getWidgetBannerByTitle($widget);
                $types = implode(',',$widgetBanner->getTypes());
                $uniqueId = md5($widgetBanner->getId());
                $widgetContent = '{{widget type="Magento\Banner\Block\Widget\Banner" types="'.$types.'" banner_ids="'.$widgetBanner->getId().'" unique_id="'.$uniqueId.'" template="widget/block.phtml"}}';
                $row['content'] = str_replace($widget, $widgetContent, $row['content']);
            }
        }
    }

    /**
     * @param $content
     */
    private function uploadImageToWysiwygFolder($row)
    {
        try {
            $content = $row['content'];
            if (preg_match_all('/{{media\surl=[^}}]*}}/i', $content, $result)) {
                $result = (isset($result[0])) ? $result[0] : [];
                foreach ($result as $media) {
                    preg_match('/url="([^"]*)"/i', $media, $path);
                    $pathImage = $path[1];

                    $imageFile = $this->fixtureManager->getFixture("$this->moduleName::fixtures/media/" . $pathImage);
                    if (!is_file($imageFile)) {
                        return '';
                    }

                    $imageFileParts = pathinfo($pathImage);

                    /** @var \Magento\Framework\Filesystem $fileSystem */
                    $fileSystem = $this->objectManager->get('Magento\Framework\Filesystem');
                    /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
                    $mediaDirectory = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
                    $absolutePath = $mediaDirectory->getAbsolutePath($imageFileParts['dirname']);
                    $fileToUpload = $absolutePath . DIRECTORY_SEPARATOR . $imageFileParts['basename'];

                    // mkdir directories if doesn't exists
                    $this->io->mkdir($absolutePath, 0775);

                    copy($imageFile, $fileToUpload);
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical('Ha fallado la subida de imagenes al folder wysiwyg');
            $this->logger->critical($e->getMessage(), $e->getTrace());
        }
    }

    /**
     * @param $widget
     * @return mixed|null
     */
    private function getSliderIdByTitle($widget)
    {
        if (!is_string($widget)) {
            return null;
        }

        $title = preg_replace('/{{widget_slider_(.*)}}/i', '$1', $widget);
        $title = str_replace('_', ' ' , $title);

        /** @var \WeltPixel\OwlCarouselSlider\Model\Slider $slider */
        $slider = $this->sliderFactory->create();
        $this->sliderResourceModel->load($slider, $title, 'title');

        return $slider->getId();
    }

    /**
     * @param $widget
     * @return \Magento\Banner\Model\Banner
     */
        private function getWidgetBannerByTitle($widget)
    {
        if (!is_string($widget)) {
            return null;
        }

        $name = preg_replace('/{{widget_banner_(.*)}}/i', '$1', $widget);
        $name = str_replace('_', ' ' , $name);

        /** @var \Magento\Banner\Model\Banner $widgetBanner */
        $widgetBanner = $this->bannerFactory->create();
        $this->bannerResourceModel->load($widgetBanner, $name, 'name');

        return $widgetBanner;
    }

    /**
     * @param $row
     */
    private function parseCustomTheme(&$row)
    {
        if (isset($row['custom_theme'])) {
            /** @var \Magento\Theme\Model\Theme $themeModel */
            $themeModel = $this->themeFactory->create();
            $this->themeResourceModel->load($themeModel, $row['custom_theme'], 'code');
            $row['custom_theme'] = $themeModel->getId();
        }
    }
}
