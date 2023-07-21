<?php

namespace Daytours\Catalog\Setup;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface
{

    private $eavSetupFactory;
    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    private $storeFactory;
    /**
     * @var \Magento\Store\Model\ResourceModel\Store
     */
    private $storeResourceModel;
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    private $resourceConfig;
    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    private $websiteFactory;
    /**
     * @var \Magento\Store\Model\ResourceModel\Website
     */
    private $websiteResourceModel;
    /**
     * @var \Woow\SetupTheme\Model\Page
     */
    private $pageInstall;
    /**
     * @var \Woow\SetupTheme\Model\Block
     */
    private $blockInstall;
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
     * @var \Magento\Store\Model\WebsiteRepository
     */
    private $websiteRepository;
    /**
     * @var \Magento\Store\Model\StoreRepository
     */
    private $storeRepository;
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;
    /**
     * @var \Woow\SetupTheme\Model\Slider
     */
    private $sliderInstall;
    /**
     * @var \Woow\SetupTheme\Model\Banner
     */
    private $bannerInstall;
    /**
     * @var \Woow\SetupTheme\Model\Website
     */
    private $websiteInstall;
    /**
     * @var \Woow\SetupTheme\Model\Store
     */
    private $storeGroupInstall;
    /**
     * @var \Woow\SetupTheme\Model\Storeview
     */
    private $storeviewInstall;
    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    private $indexerFactory;
    /**
     * @var \Magento\Indexer\Model\Indexer\CollectionFactory
     */
    private $indexerCollectionFactory;
    /**
     * @var \Woow\SetupTheme\Model\DesignConfig
     */
    private $designConfigInstall;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Amasty\Base\Helper\Deploy
     */
    private $pubDeployer;
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;
    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    private $categoryRepository;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    private $categoryFactory;
    /**
     * @var \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    private $categoryLinkManagement;
    /**
     * @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface
     */
    private $stockItemRepository;
    /**
     * @var \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory
     */
    private $stockItemFactory;
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;
    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    private $blockFactory;
    /**
     * @var \Magento\Cms\Model\BlockRepository
     */
    private $blockRepository;

    /**
     * UpgradeData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Store\Model\WebsiteRepository $websiteRepository
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Store\Model\ResourceModel\Website $websiteResourceModel
     * @param \Magento\Store\Model\ResourceModel\Store $storeResourceModel
     * @param \Magento\Store\Model\StoreRepository $storeRepository
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Theme\Model\ThemeFactory $themeFactory
     * @param \Magento\Theme\Model\ResourceModel\Theme $themeResourceModel
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Indexer\Model\IndexerFactory $indexerFactory
     * @param \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Amasty\Base\Helper\Deploy $pubDeployer
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory
     * @param \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Store\Model\WebsiteRepository $websiteRepository,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Store\Model\ResourceModel\Website $websiteResourceModel,
        \Magento\Store\Model\ResourceModel\Store $storeResourceModel,
        \Magento\Store\Model\StoreRepository $storeRepository,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Theme\Model\ThemeFactory $themeFactory,
        \Magento\Theme\Model\ResourceModel\Theme $themeResourceModel,
        \Magento\Framework\App\State $state,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Amasty\Base\Helper\Deploy $pubDeployer,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement,
        \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory,
        \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Cms\Model\BlockRepository $blockRepository
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->storeFactory = $storeFactory;
        $this->storeResourceModel = $storeResourceModel;
        $this->resourceConfig = $resourceConfig;
        $this->websiteFactory = $websiteFactory;
        $this->websiteResourceModel = $websiteResourceModel;
        $this->logger = $logger;
        $this->themeFactory = $themeFactory;
        $this->themeResourceModel = $themeResourceModel;
        $this->websiteRepository = $websiteRepository;
        $this->storeRepository = $storeRepository;
        $this->state = $state;
        $this->indexerFactory = $indexerFactory;
        $this->indexerCollectionFactory = $indexerCollectionFactory;
        $this->registry = $registry;
        $this->pubDeployer = $pubDeployer;
        $this->filesystem = $filesystem;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->categoryFactory = $categoryFactory;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockItemFactory = $stockItemFactory;
        $this->stockRegistry = $stockRegistry;
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        // Creating cms clock to categories on home search
        if (version_compare($context->getVersion(), '1.0.1') < 0)
        {

            $stores = [];

            /** @var  \Magento\Store\Model\Store $storeView */
            $storeViewEnglish = $this->storeFactory->create();
            $this->storeResourceModel->load($storeViewEnglish, 'english', 'code');
            if ($storeViewEnglish->getId()) {
                $stores[] = $storeViewEnglish->getId();
            }

            $storeViewSpanish = $this->storeFactory->create();
            $this->storeResourceModel->load($storeViewSpanish, 'spanish', 'code');
            if ($storeViewSpanish->getId()) {
                $stores[] = $storeViewSpanish->getId();
            }

            $storeViewFrench = $this->storeFactory->create();
            $this->storeResourceModel->load($storeViewFrench, 'french', 'code');
            if ($storeViewFrench->getId()) {
                $stores[] = $storeViewFrench->getId();
            }

            $storeViewPortuguese = $this->storeFactory->create();
            $this->storeResourceModel->load($storeViewPortuguese, 'portuguese', 'code');
            if ($storeViewPortuguese->getId()) {
                $stores[] = $storeViewPortuguese->getId();
            }

            $data = [
                'title' => 'Categories For mini search in home page',
                'identifier' => 'categories-mini-search-home',
                'stores' => $stores,
                'is_active' => 1,
                'content' => '{{block class="Daytours\Catalog\Block\CategoriesToSearchHome" id_category_parent="53"}}'
            ];
            $newBlock = $this->blockFactory->create(['data' => $data]);
            $this->blockRepository->save($newBlock);
        }

        $setup->endSetup();
    }
}