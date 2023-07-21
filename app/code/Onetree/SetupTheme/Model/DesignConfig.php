<?php

namespace Onetree\SetupTheme\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

class DesignConfig
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
    protected $Factory;
    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $filterManager;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Magento\Store\Model\ResourceModel\Website
     */
    private $websiteResourceModel;
    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    private $websiteFactory;
    /**
     * @var \Magento\Store\Model\WebsiteRepository
     */
    private $websiteRepository;
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
     * @var \Magento\Theme\Model\Data\Design\ConfigFactory
     */
    private $configFactory;
    /**
     * @var \Magento\Theme\Model\DesignConfigRepository
     */
    private $designConfigRepository;

    /**
     * DesignConfig constructor.
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\ResourceModel\Website $websiteResourceModel
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Store\Model\WebsiteRepository $websiteRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Theme\Model\ThemeFactory $themeFactory
     * @param \Magento\Theme\Model\ResourceModel\Theme $themeResourceModel
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Store\Model\ResourceModel\Store $storeResourceModel
     * @param \Magento\Store\Model\StoreRepository $storeRepository
     * @param \Magento\Theme\Model\Data\Design\ConfigFactory $configFactory
     * @param \Magento\Theme\Model\DesignConfigRepository $designConfigRepository
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\ResourceModel\Website $websiteResourceModel,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Store\Model\WebsiteRepository $websiteRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Theme\Model\ThemeFactory $themeFactory,
        \Magento\Theme\Model\ResourceModel\Theme $themeResourceModel,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Store\Model\ResourceModel\Store $storeResourceModel,
        \Magento\Store\Model\StoreRepository $storeRepository,
        \Magento\Theme\Model\Data\Design\ConfigFactory $configFactory,
        \Magento\Theme\Model\DesignConfigRepository $designConfigRepository
    )
    {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();

        $this->filterManager = $filterManager;
        $this->objectManager = $objectManager;
        $this->websiteResourceModel = $websiteResourceModel;
        $this->websiteFactory = $websiteFactory;
        $this->websiteRepository = $websiteRepository;
        $this->logger = $logger;
        $this->themeFactory = $themeFactory;
        $this->themeResourceModel = $themeResourceModel;
        $this->storeFactory = $storeFactory;
        $this->storeResourceModel = $storeResourceModel;
        $this->storeRepository = $storeRepository;
        $this->configFactory = $configFactory;
        $this->designConfigRepository = $designConfigRepository;
    }

    /**
     * @param array $fixtures
     * @throws \Exception
     */
    public function install(array $fixtures)
    {
        foreach ($fixtures as $fileName) {
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

                $design['design'] = $row;

                $this->processDesignConfigSave($design);
            }
        }
    }

    /**
     * Process Website model save
     *
     * @param $postData
     * @return array
     */
    private function processDesignConfigSave($postData)
    {
        if (isset($postData['design']['theme_theme_code']) && !empty($postData['design']['theme_theme_code'])) {
            /** @var \Magento\Theme\Model\Theme $themeModel */
            $themeModel = $this->themeFactory->create();
            $this->themeResourceModel->load($themeModel, $postData['design']['theme_theme_code'], 'code');
            $postData['design']['theme_theme_id'] = ($themeModel->getId()) ? $themeModel->getId() : '';
        }

        if ($postData['design']['scope'] == 'websites') {
            /** @var \Magento\Store\Model\Website $websiteModel */
            $websiteModel = $this->websiteFactory->create();
            $this->websiteResourceModel->load($websiteModel, $postData['design']['scope_code'], 'code');
            $postData['design']['scope_id'] = ($websiteModel->getId()) ? $websiteModel->getId() : '';
        }

        if ($postData['design']['scope'] == 'stores') {
            /** @var \Magento\Store\Model\Store $store */
            $store = $this->storeFactory->create();
            $this->storeResourceModel->load($store, $postData['design']['scope_code']);
            $postData['design']['scope_id'] = ($store->getId()) ? $store->getId() : '';
        }

        $designConfigData = $this->configFactory->create($postData['design']['scope'], $postData['design']['scope_id'], $postData['design']);
        $this->designConfigRepository->save($designConfigData);

        return $postData;
    }
}
