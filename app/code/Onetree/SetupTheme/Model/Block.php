<?php

namespace Onetree\SetupTheme\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

/**
 * Class Block
 */
class Block
{
    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    protected $fixtureManager;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * @var Block\Converter
     */
    protected $converter;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $storeFactory;
    /**
     * @var \Magento\Store\Model\ResourceModel\Store
     */
    protected $storeResourceModel;
    /**
     * @var \Magento\Store\Model\StoreRepository
     */
    protected $storeRepository;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $io;
    /**
     * @var string
     */
    private $moduleName;

    /**
     * Block constructor.
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param Block\Converter $converter
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Store\Model\ResourceModel\Store $storeResourceModel
     * @param \Magento\Store\Model\StoreRepository $storeRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        Block\Converter $converter,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Store\Model\ResourceModel\Store $storeResourceModel,
        \Magento\Store\Model\StoreRepository $storeRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Filesystem\Io\File $io
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->blockFactory = $blockFactory;
        $this->converter = $converter;
        $this->categoryRepository = $categoryRepository;
        $this->storeFactory = $storeFactory;
        $this->storeResourceModel = $storeResourceModel;
        $this->storeRepository = $storeRepository;
        $this->logger = $logger;
        $this->objectManager = $objectManager;
        $this->io = $io;
    }

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
                $data = $this->converter->convertRow($row);
                // copy image if exists medi url, ex {{media url="wysiwyg/test/testimage.jpg"}}
                $this->uploadImageToWysiwygFolder($data['block'], $moduleName);
                $cmsBlock = $this->saveCmsBlock($data['block']);
                $cmsBlock->unsetData();
            }
        }
    }

    /**
     * @param array $data
     * @return \Magento\Cms\Model\Block
     */
    protected function saveCmsBlock($data)
    {
        $cmsBlock = $this->blockFactory->create();
        $cmsBlock->getResource()->load($cmsBlock, $data['identifier']);
        if (!$cmsBlock->getData()) {
            $cmsBlock->setData($data);
        } else {
            $cmsBlock->addData($data);
        }

        $stores = $this->getStores($data);

        $cmsBlock->setStores($stores);
        $cmsBlock->setIsActive(1);
        $cmsBlock->save();
        return $cmsBlock;
    }

    /**
     * @param string $blockId
     * @param string $categoryId
     * @return void
     */
    protected function setCategoryLandingPage($blockId, $categoryId)
    {
        $categoryCms = [
            'landing_page' => $blockId,
            'display_mode' => 'PRODUCTS_AND_PAGE',
        ];
        if (!empty($categoryId)) {
            $category = $this->categoryRepository->get($categoryId);
            $category->setData($categoryCms);
            $this->categoryRepository->save($categoryId);
        }
    }

    /**
     * @param $row
     * @return array
     */
    protected function getStores($row)
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
     * @param $row
     * @param $moduleName
     * @return string
     */
    protected function uploadImageToWysiwygFolder($row, $moduleName)
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
}
