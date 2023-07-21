<?php

namespace Daytours\Bookingsystem\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Registry;

use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Catalog\Setup\CategorySetupFactory;

use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\App\State;


class InstallData implements InstallDataInterface
{
     /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var EavSetupFactory
     */
    protected $_eavSetupFactory;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;
    /**
     * @var ProductFactory
     */
    private $_product;
    /**
     * @var Option
     */
    private $_option;
    /**
     * @var State
     */
    private $_state;

    /**
     * InstallData constructor.
     * @param Registry $registry
     * @param EavSetupFactory $eavSetupFactory
     * @param ObjectManagerInterface $objectManager
     * @param CategorySetupFactory $categorySetupFactory
     * @param ProductFactory $product
     * @param Option $option
     * @param State $state
     */

    public function __construct(
        Registry $registry,
        EavSetupFactory $eavSetupFactory,
        ObjectManagerInterface $objectManager,
        CategorySetupFactory $categorySetupFactory,
        ProductFactory $product,
        Option $option,
        State $state)
    {

        $this->_registry = $registry;
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->objectManager = $objectManager;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->_product = $product;
        $this->_option = $option;
        $this->_state = $state;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        //$this->_state->setAreaCode('frontend');
        $setup->startSetup();

        $this->addAttributeSetTransfer($setup);
        $this->createProductType($setup);
        //$this->createProductBaseWithCustomOptions();

        $setup->endSetup();
    }


    /**
     * Add attribute set transfer
     *
     * @return void
     */
    protected function addAttributeSetTransfer($setup)
    {
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);
        $entityTypeId = $eavSetup->getEntityTypeId('catalog_product');
        $defaultSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $data = [
            'attribute_set_name' => 'Transfer',
            'entity_type_id' => $entityTypeId,
            'sort_order' => 0
        ];
        $transferSet = $eavSetup->getAttributeSet($entityTypeId, $data['attribute_set_name'], 'attribute_set_name');
        if (!$transferSet) {
            $transferSet = $this->objectManager->create(AttributeSet::class);
            $transferSet->setData($data);
            $transferSet->validate();
            $transferSet->save();
            $transferSet->initFromSkeleton($defaultSetId);
            $transferSet->save();
        }
    }

    protected function createProductType($setup){
        /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
//        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
//
//        $fieldList = array(
//            'price',
////                'special_price',
////                'special_from_date',
////                'special_to_date',
////                'minimal_price',
////                'cost',
////                'tier_price',
////                'weight',
//            'tax_class_id'
//        );
//
//        foreach ($fieldList as $field) {
//            $applyTo = explode(',', $categorySetup->getAttribute('catalog_product', $field, 'apply_to'));
//            if (!in_array(\Daytours\TransferProduct\Model\Product\Type\Transfer::PRODUCT_TYPE, $applyTo)) {
//                $applyTo[] = \Daytours\TransferProduct\Model\Product\Type\Transfer::PRODUCT_TYPE;
//                $categorySetup->updateAttribute('catalog_product', $field, 'apply_to', implode(',', $applyTo));
//            }
//        }
    }

    private function createProductBaseWithCustomOptions(){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->create(\Magento\Catalog\Model\Product::class);

        $product->setName('Base Transfer')
            ->setTypeId('transfer')
            ->setAttributeSetId(4)
            ->setSku('base-transfer')
            ->setUrlKey('base-transfer')
            ->setCanSaveCustomOptions(true)
            ->setHasOptions(true)
            ->setWebsiteIds(array(1))
            ->setVisibility(4)
            ->setPrice(10)
            ->setStockData(array(
                'is_in_stock' => 1,
                'qty' => 100
            )
        );

        $options = [
            [
                "title"         => "No. of adults:",
                "type"          => "drop_down",
                "is_require"    => true,
                "sort_order"    => 1,
                "values"        => [
                    [
                        'title'         =>  '1',
                        'price'         =>  0,
                        'price_type'    =>  "fixed",
                        "sku"           =>  'no-of-adults-1',
                        'sort_order'    =>  1
                    ],
                    [
                        'title'         =>  '2',
                        'price'         =>  0,
                        'price_type'    =>  "fixed",
                        "sku"           =>  'no-of-adults-2',
                        'sort_order'    =>  2
                    ],
                    [
                        'title'         =>  '3',
                        'price'         =>  0,
                        'price_type'    =>  "fixed",
                        "sku"           =>  'no-of-adults-3',
                        'sort_order'    =>  3
                    ],
                    [
                        'title'         =>  '4',
                        'price'         =>  12,
                        "sku"           =>  'no-of-adults-4',
                        'price_type'    =>  "fixed",
                        'sort_order'    =>  4
                    ],
                    [
                        'title'         =>  '5-15',
                        'price'         =>  230,
                        'price_type'    =>  "fixed",
                        "sku"           =>  'no-of-adults-5-15',
                        'sort_order'    =>  5
                    ],
                    [
                        'title'         =>  '16-19',
                        'price'         =>  377,
                        'price_type'    =>  "fixed",
                        "sku"           => 'no-of-adults-16-19',
                        'sort_order'    =>  6
                    ]
                ]
            ],
            [
                'title' => 'Date of arrival:',
                'type' => 'date',
                'price' => 0,
                'price_type' => 'fixed',
                'sku' => 'date',
                'is_require' => true,
                'sort_order' => 2
            ],
            [
                'title' => 'Time of arrival:',
                'type' => 'time',
                'price' => 0,
                'price_type' => 'fixed',
                'is_require' => true,
                'sku' => 'time',
                'sort_order' => 3
            ],
            [
                'title' => 'Arrival flight no:',
                'type' => 'field',
                'is_require' => true,
                'sort_order' => 4,
                'price' => 0,
                'price_type' => 'fixed',
                'sku' => 'arrival-flight-no',
                'max_characters' => 10,
            ],
            [
                "title"         => "Drop-off location:",
                "type"          => "drop_down",
                "is_require"    => false,
                "sort_order"    => 5,
                "values"        => [
                    [
                        'title'         =>  'Belgrano',
                        'price'         =>  10,
                        "sku"           =>  'drop-off-location-belgrano',
                        'price_type'    =>  "fixed",
                        'sort_order'    =>  1
                    ],
                    [
                        'title'         =>  'Palermo',
                        'price'         =>  10,
                        'price_type'    =>  "fixed",
                        "sku"           =>  'drop-off-location-palermo',
                        'sort_order'    =>  2
                    ]
                ]
            ],
            [
                'title' => 'Date of departure:',
                'type' => 'date',
                'price' => 0,
                'price_type' => 'fixed',
                'sku' => 'date-of-departure',
                'is_require' => true,
                'sort_order' => 6
            ],
            [
                'title' => 'Time of departure:',
                'type' => 'time',
                'price' => 0,
                'price_type' => 'fixed',
                'is_require' => true,
                'sku' => 'time-of-departure',
                'sort_order' => 7
            ],
            [
                'title' => 'Departure flight no.',
                'type' => 'field',
                'is_require' => true,
                'sort_order' => 8,
                'price' => 0,
                'price_type' => 'fixed',
                'sku' => 'departure-flight-no',
                'max_characters' => 10,
            ],
            [
                "title"         => "Pick-up location:",
                "type"          => "drop_down",
                "is_require"    => false,
                "sort_order"    => 9,
                "values"        => [
                    [
                        'title'         =>  'Belgrano',
                        'price'         =>  10,
                        "sku"           =>  'pick-up-location-belgrano',
                        'price_type'    =>  "fixed",
                        'sort_order'    =>  1
                    ],
                    [
                        'title'         =>  'Palermo',
                        'price'         =>  10,
                        'price_type'    =>  "fixed",
                        "sku"           =>  'pick-up-location-palermo',
                        'sort_order'    =>  2
                    ]
                ]
            ],
            [
                'title' => 'Hotel name / add:',
                'type' => 'field',
                'is_require' => true,
                'sort_order' => 10,
                'price' => 0,
                'price_type' => 'fixed',
                'sku' => 'hotel-name',
                'max_characters' => 100,
            ]
        ];

        $customOptionFactory = $objectManager->create(\Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory::class);


        $customOptions = [];

        foreach ($options as $arrayOption) {

            $customOption = $customOptionFactory->create(['data' => $arrayOption]);
            $customOption->setProductSku($product->getSku());
            $customOptions[] = $customOption;

        }
        $product->setOptions($customOptions);
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryFactory */
        $productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $productRepository->save($product);

    }

}