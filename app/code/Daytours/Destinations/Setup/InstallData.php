<?php

namespace Daytours\Destinations\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\TestFramework\Event\Magento;
use Psr\Log\LoggerInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\Product\Action as ProductAction;

use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Store\Model\WebsiteRepository;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Daytours\Bookingsystem\Helper\ProductType;

class InstallData implements InstallDataInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Page factory
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    private $attributeSet;
    private $websiteRepository;
    private $websiteCollectionFactory;
    private $productCollectionFactory;
    private $productAction;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * UpgradeData constructor.
     * @param ObjectManagerInterface $manager
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeSet $attributeSet
     * @param WebsiteRepository $websiteRepository
     * @param WebsiteCollectionFactory $websiteCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductAction $productAction
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\App\State $state
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        ObjectManagerInterface $manager,
        EavSetupFactory $eavSetupFactory,
        AttributeSet $attributeSet,
        WebsiteRepository $websiteRepository,
        WebsiteCollectionFactory $websiteCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        ProductAction $productAction,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\State $state
    ) {
        $this->objectManager = $manager;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSet = $attributeSet;
        $this->websiteRepository = $websiteRepository;
        $this->websiteCollectionFactory = $websiteCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productAction = $productAction;
        $this->logger = $this->objectManager->get('Psr\Log\LoggerInterface');
        $this->productRepository = $productRepository;
        $this->state = $state;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context){
        $setup->startSetup();


        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $groupName = 'FILTERS';
        $entityTypeId = $eavSetup->getEntityTypeId('catalog_product');
        $data = [
            'attribute_set_name' => ProductType::TRANSFER,
            'entity_type_id' => $entityTypeId,
            'sort_order' => 0
        ];
        $transferSet = $eavSetup->getAttributeSet($entityTypeId, $data['attribute_set_name'], 'attribute_set_name');
        $defaultSet = $eavSetup->getAttributeSet($entityTypeId, 'Default', 'attribute_set_name');

        if ($transferSet) {
            $transferSetId = $eavSetup->getAttributeSetId($entityTypeId, $transferSet);
            $defaultSetId = $eavSetup->getAttributeSetId($entityTypeId, $defaultSet);

            $eavSetup->addAttributeGroup($entityTypeId, $transferSetId, $groupName);
            $eavSetup->addAttributeGroup($entityTypeId, $defaultSetId, $groupName);

            if(!$eavSetup->getAttributeId($entityTypeId, 'destination_filter'))
            {
                $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    'destination_filter',
                    [
                        'type' => 'int',
                        'label' => 'Associated destination',
                        'input' => 'multiselect',
                        'unit' => '',
                        'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                        'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                        'visible' => true,
                        'required' => false,
                        'user_defined' => true,
                        'searchable' => true,
                        'filterable' => true,
                        'filterable_in_search' => true,
                        'comparable' => false,
                        'visible_on_front' => true,
                        'used_in_product_listing' => true,
                        'unique' => false,
                        'source' => 'Daytours\Destinations\Model\Config\Source\DestinationOptions',
                        'apply_to' => '',
                    ]
                );
                $eavSetup->addAttributeToSet(
                    $entityTypeId,
                    $transferSetId,
                    $groupName,
                    $eavSetup->getAttributeId($entityTypeId, 'destination_filter')
                );
                $eavSetup->addAttributeToSet(
                    $entityTypeId,
                    $defaultSetId,
                    $groupName,
                    $eavSetup->getAttributeId($entityTypeId, 'destination_filter')
                );
            }
        }

        $setup->endSetup();
    }

}