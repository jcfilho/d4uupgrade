<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Product\Initialization;
 
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory as CustomOptionFactory;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory as ProductLinkFactory;
use Magento\Catalog\Api\ProductRepositoryInterface\Proxy as ProductRepository;
use Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks;
use Magento\Catalog\Model\Product\Link\Resolver as LinkResolver;
use Magento\Downloadable\Api\Data\SampleInterfaceFactory as SampleFactory;
use Magento\Downloadable\Api\Data\LinkInterfaceFactory as LinkFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Api\Data\ProductInterface;

class Helper {

    /**
     * The greatest value which could be stored in CatalogInventory Qty field
     */
    const MAX_QTY_VALUE = 99999999.9999;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;
	
	/**
	 * @var \Magento\Framework\App\RequestInterface
	 */
	protected $request;

	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $storeManager;

	/**
	 * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
	 *
	 * @deprecated
	 */
	protected $dateFilter;

	/**
	 * @var CustomOptionFactory
	 */
	protected $customOptionFactory;

	/**
	 * @var ProductLinkFactory
	 */
	protected $productLinkFactory;

	/**
	 * @var ProductRepository
	 */
	protected $productRepository;

	/**
	 * @var ProductLinks
	 */
	protected $productLinks;

	/**
	 * @var LinkResolver
	 */
	private $linkResolver;
	
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
	protected $_objectManager;

	/**
	 * @var \Magento\Framework\Stdlib\DateTime\Filter\DateTime
	 */
	private $dateTimeFilter;
	
    /**
     * @var \Magento\Downloadable\Model\Link\Builder
     */
    private $linkBuilder;
	
    /**
     * @var LinkFactory
     */
    private $linkFactory;
	
    /**
     * @var \Magento\Downloadable\Model\Sample\Builder
     */
    private $sampleBuilder;	
	
    /**
     * @var SampleFactory
     */
    private $sampleFactory;
	
    /**
     * @var VariationHandler
     */
    private $variationHandler;
	
    /**
     * @var Factory
     */
    private $optionsFactory;

	/**
	 * Helper constructor.
	 * @param \Magento\Framework\App\RequestInterface $request
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param ProductLinks $productLinks
	 * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
	 */
	public function __construct(
		\Magento\Framework\App\RequestInterface $request,
		ObjectManagerInterface $objectManager,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $productLinks,
		\Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        ScopeConfigInterface $scopeConfig,
        StockConfigurationInterface $stockConfiguration,
		\Magento\ConfigurableProduct\Model\Product\VariationHandler $variationHandler,
		\Magento\ConfigurableProduct\Helper\Product\Options\Factory $optionsFactory
	) {
		$this->_objectManager = $objectManager;        
        $this->scopeConfig = $scopeConfig;
        $this->stockConfiguration = $stockConfiguration;		
		$this->request = $request;
		$this->storeManager = $storeManager;
		$this->productLinks = $productLinks;
		$this->dateFilter = $dateFilter;
		$this->variationHandler = $variationHandler;
		$this->optionsFactory = $optionsFactory;
	}

	/**
	 * @param \Magento\Catalog\Model\Product $product
	 * @return \Magento\Catalog\Model\Product
	 */
	public function productInitialize(\Magento\Catalog\Model\Product $product) {
		$productData = $this->request->getPost('product', []);
		$productData['status']=1;		
		/*$productData['meta_title']=$productData['name'];
		$productData['meta_keyword']=$productData['name'];
		$productData['meta_description']=$productData['name'];*/
		$productData['website_ids']=array(1);
		$productData['msrp_display_actual_price_type']=0;		
		$productData['stock_data']['use_config_manage_stock']=1;
		/*if(array_key_exists('qty',$productData['quantity_and_stock_status'])){
			$productData['stock_data']['qty']=(int)$productData['quantity_and_stock_status']['qty'];			
		}
		$productData['stock_data']['is_in_stock']=(int)$productData['quantity_and_stock_status']['is_in_stock'];
		$productData['stock_data']['min_qty']=0;
		$productData['stock_data']['use_config_min_qty']=1;
		$productData['stock_data']['min_sale_qty']=1;
		$productData['stock_data']['use_config_min_sale_qty']=1;
		$productData['stock_data']['max_sale_qty']=10000;
		$productData['stock_data']['use_config_max_sale_qty']=1;
		$productData['stock_data']['is_qty_decimal']=1;
		$productData['stock_data']['is_decimal_divided']=1;
		$productData['stock_data']['backorders']=0;
		$productData['stock_data']['use_config_backorders']=1;
		$productData['stock_data']['notify_stock_qty']=1;
		$productData['stock_data']['use_config_notify_stock_qty']=1;
		$productData['stock_data']['enable_qty_increments']=0;
		$productData['stock_data']['use_config_enable_qty_increments']=1;
		$productData['stock_data']['qty_increments']=1;
		$productData['stock_data']['use_config_qty_increments']=1;
		$productData['options_container']='container2';		
		$productData['use_config_gift_message_available']=1;*/
		if(isset($productData['custom_attributes'])) {
			unset($productData['custom_attributes']);
		}
		if(isset($productData['extension_attributes'])) {
			unset($productData['extension_attributes']);
		}
		if ($productData) {
			$stockData = isset($productData['stock_data']) ? $productData['stock_data'] : [];
			$productData['stock_data'] = $this->productStockFilter($stockData);
		}
        $productData = $this->normalizeProductData($productData);
        if (!empty($productData['is_downloadable'])) {
            $productData['product_has_weight'] = 0;
        }
        foreach (['category_ids', 'website_ids'] as $field) {
            if (!isset($productData[$field])) {
                $productData[$field] = [];
            }
        }
        foreach ($productData['website_ids'] as $websiteId => $checkboxValue) {
            if (!$checkboxValue) {
                unset($productData['website_ids'][$websiteId]);
            }
        }
        $wasLockedMedia = false;
        if ($product->isLockedAttribute('media')) {
            $product->unlockAttribute('media');
            $wasLockedMedia = true;
        }
		$productData = $this->__productDateTimeFilter($product, $productData);
        if (isset($productData['options'])) {
            $productOptions = $productData['options'];
            unset($productData['options']);
        } else {
            $productOptions = [];
        }
		$product->addData($productData);
        if ($wasLockedMedia) {
            $product->lockAttribute('media');
        }
        $useDefaults = (array)$this->request->getPost('use_default', []);
        foreach ($useDefaults as $attributeCode => $useDefaultState) {
            if ($useDefaultState) {
                $product->setData($attributeCode, null);
                // UI component sends value even if field is disabled, so 'Use Config Settings' must be reset to false
                if ($product->hasData('use_config_' . $attributeCode)) {
                    $product->setData('use_config_' . $attributeCode, false);
                }
            }
        }
		$product = $this->__buildProductLinks($product);
        if ($productOptions && !$product->getOptionsReadonly()) {
            // mark custom options that should to fall back to default value
            $options = $this->mergeProductOptions(
                $productOptions,
                $this->request->getPost('options_use_default')
            );
            $customOptions = [];
            foreach ($options as $customOptionData) {
                if (empty($customOptionData['is_delete'])) {
                    if (isset($customOptionData['values'])) {
                        $customOptionData['values'] = array_filter($customOptionData['values'], function ($valueData) {
                            return empty($valueData['is_delete']);
                        });
                    }
					if (null === $this->customOptionFactory) {
						$this->customOptionFactory = $this->_objectManager->get('Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory');
					}
                    $customOption = $this->customOptionFactory->create(['data' => $customOptionData]);
                    $customOption->setProductSku($product->getSku());
                    $customOption->setOptionId(null);
                    $customOptions[] = $customOption;
                }
            }
            $product->setOptions($customOptions);
        }
        $product->setCanSaveCustomOptions(
            (bool)$this->request->getPost('affect_product_custom_options') && !$product->getOptionsReadonly()
        );		
		$product = $this->buildDownloadableProduct($product);
		$product = $this->buildConfigProduct($product);
		$product = $this->updateConfigurations($product);
		
		return $product;
	}	
	
	/**
	 * @param \Magento\Catalog\Model\Product $product
	 * @return \Magento\Catalog\Model\Product
	 */
	protected function buildConfigProduct(\Magento\Catalog\Model\Product $product) {
        $attributes = $this->request->getParam('attributes');
        $productData = $this->request->getPost('product', []);
        if ($product->getTypeId() !== ConfigurableProduct::TYPE_CODE || empty($attributes)) {
            return $product;
        }
        $setId = $this->request->getPost('set');
        if ($setId) {
            $product->setAttributeSetId($setId);
        }
        $extensionAttributes = $product->getExtensionAttributes();
        $product->setNewVariationsAttributeSetId($setId);
        $configurableOptions = [];
        if (!empty($productData['configurable_attributes_data'])) {
            $configurableOptions = $this->optionsFactory->create(
                (array) $productData['configurable_attributes_data']
            );
        }
        $extensionAttributes->setConfigurableProductOptions($configurableOptions);
        /*$associatedProductIds = $product->hasData('associated_product_ids') ? $product->getData('associated_product_ids') : [];*/
		$associatedProductIds = $this->request->getPost('associated_product_ids', []);
        $variationsMatrix = $this->getVariationMatrixFromProductConfig();
        if ($associatedProductIds || $variationsMatrix) {
            $this->variationHandler->prepareAttributeSet($product);
        }
        if (!empty($variationsMatrix)) {
            $generatedProductIds = $this->variationHandler->generateSimpleProducts($product, $variationsMatrix);
            $associatedProductIds = array_merge($associatedProductIds, $generatedProductIds);
        }
        $extensionAttributes->setConfigurableProductLinks(array_filter($associatedProductIds));
        $product->setCanSaveConfigurableAttributes(
            (bool) $this->request->getPost('affect_configurable_product_attributes')
        );
        $product->setExtensionAttributes($extensionAttributes);
        
		return $product;
	}
	
	/**
	 * @return \Magento\Catalog\Model\Product
	 */
	protected function updateConfigurations(\Magento\Catalog\Model\Product $product) {
        $configurations = $this->getConfigurationsFromProduct($product);
        $configurations = $this->variationHandler->duplicateImagesForVariations($configurations);
        if (count($configurations)) {
            foreach ($configurations as $productId => $productData) {
                /** @var \Magento\Catalog\Model\Product $product */
                $__product = $this->getProductRepository()->getById($productId, false, $this->request->getParam('store', 0));
                $productData = $this->variationHandler->processMediaGallery($__product, $productData);
                $__product->addData($productData);
                if ($__product->hasDataChanges()) {
                    $__product->save();
                }
            }
        }
		return $product;
	}
	
    /**
     * Get configurations from product
     *
     * @param \Magento\Catalog\Model\Product $configurableProduct
     * @return array
     */
    private function getConfigurationsFromProduct(\Magento\Catalog\Model\Product $configurableProduct)
    {
        $result = $this->request->getParam('configurations', []);
        return $result;
    }
	
    /**
     * Get variation-matrix from product
     *
     * @param ProductInterface $product
     * @return array
     */
    private function getVariationMatrixFromProductConfig()
    {
        $result = $this->request->getParam('variations-matrix', []);
        return $result;
    }
	
	/**
	 * @param \Magento\Catalog\Model\Product $product
	 * @return \Magento\Catalog\Model\Product
	 */
	public function buildDownloadableProduct(\Magento\Catalog\Model\Product $product) {
        if ($downloadable = $this->request->getPost('downloadable')) {
            $product->setDownloadableData($downloadable);
            $extension = $product->getExtensionAttributes();
            if (isset($downloadable['link']) && is_array($downloadable['link'])) {
                $links = [];
                foreach ($downloadable['link'] as $linkData) {
                    if (!$linkData || (isset($linkData['is_delete']) && $linkData['is_delete'])) {
                        continue;
                    } else {
						if ($linkData['link_id'] == 0) {
							unset($linkData['link_id']);
							$linkData['record_id'] = 0;
						}
						$linkData['file'] = $this->_objectManager->get(
							'Magento\Framework\Json\Helper\Data'
						)->jsonDecode($linkData['file']);
						$linkData['sample']['file'] = $this->_objectManager->get(
							'Magento\Framework\Json\Helper\Data'
						)->jsonDecode($linkData['sample']['file']);
						if (!$this->linkFactory) {
							$this->linkFactory = ObjectManager::getInstance()->get(LinkFactory::class);
						}
						if (!$this->linkBuilder) {
							$this->linkBuilder = ObjectManager::getInstance()->get(\Magento\Downloadable\Model\Link\Builder::class);
						}
                        $links[] = $this->linkBuilder->setData(
                            $linkData
                        )->build(
                            $this->linkFactory->create()
                        );
                    }
                }
                $extension->setDownloadableProductLinks($links);
            }
            if (isset($downloadable['sample']) && is_array($downloadable['sample'])) {
                $samples = [];
                foreach ($downloadable['sample'] as $sampleData) {
                    if (!$sampleData || (isset($sampleData['is_delete']) && (bool)$sampleData['is_delete'])) {
                        continue;
                    } else {
						if (!$this->sampleFactory) {
							$this->sampleFactory = ObjectManager::getInstance()->get(SampleFactory::class);
						}					
						if ($sampleData['sample_id'] == 0) {
							unset($sampleData['sample_id']);
							$sampleData['record_id'] = 0;
						}
						$sampleData['file'] = $this->_objectManager->get(
							'Magento\Framework\Json\Helper\Data'
						)->jsonDecode($sampleData['file']);
						if (!$this->sampleBuilder) {
							$this->sampleBuilder = ObjectManager::getInstance()->get(
								\Magento\Downloadable\Model\Sample\Builder::class
							);
						}
                        $samples[] = $this->sampleBuilder->setData(
                            $sampleData
                        )->build(
                            $this->sampleFactory->create()
                        );
                    }
                }
                $extension->setDownloadableProductSamples($samples);
            }
            $product->setExtensionAttributes($extension);
            if ($product->getLinksPurchasedSeparately()) {
                $product->setTypeHasRequiredOptions(true)->setRequiredOptions(true);
            } else {
                $product->setTypeHasRequiredOptions(false)->setRequiredOptions(false);
            }
        }
        return $product;	
	}
	
    /**
     * @return ProductRepository
     */
    private function getProductRepository()
    {
        if (null === $this->productRepository) {
            $this->productRepository = $this->_objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface');
        }
        return $this->productRepository;
    }
	
    /**
     * Merge product and default options for product
     *
     * @param array $productOptions product options
     * @param array $overwriteOptions default value options
     * @return array
     */
    public function mergeProductOptions($productOptions, $overwriteOptions)
    {
        if (!is_array($productOptions)) {
            return [];
        }
        if (!is_array($overwriteOptions)) {
            return $productOptions;
        }
        foreach ($productOptions as $index => $option) {
            $optionId = $option['option_id'];
            if (!isset($overwriteOptions[$optionId])) {
                continue;
            }
            foreach ($overwriteOptions[$optionId] as $fieldName => $overwrite) {
                if ($overwrite && isset($option[$fieldName]) && isset($option['default_' . $fieldName])) {
                    $productOptions[$index][$fieldName] = $option['default_' . $fieldName];
                }
            }
        }
        return $productOptions;
    }
	
    /**
     * Setting product links
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */	
    protected function __buildProductLinks(\Magento\Catalog\Model\Product $product)
    {
        if (!is_object($this->linkResolver)) {
            $this->linkResolver = ObjectManager::getInstance()->get(LinkResolver::class);
        }	
        $links = $this->linkResolver->getLinks();
        $product->setProductLinks([]);
        $product = $this->productLinks->initializeLinks($product, $links);
        $productLinks = $product->getProductLinks();
        $linkTypes = [
            'related' => $product->getRelatedReadonly(),
            'upsell' => $product->getUpsellReadonly(),
            'crosssell' => $product->getCrosssellReadonly()
        ];
        foreach ($linkTypes as $linkType => $readonly) {
            if (isset($links[$linkType]) && !$readonly) {
                foreach ((array) $links[$linkType] as $linkData) {
                    if (empty($linkData['id'])) {
                        continue;
                    }
                    $linkProduct = $this->getProductRepository()->getById($linkData['id']);
					if (null === $this->productLinkFactory) {
						$this->productLinkFactory = $this->_objectManager->get('Magento\Catalog\Api\Data\ProductLinkInterfaceFactory');
					}					
                    $link = $this->productLinkFactory->create();
                    $link->setSku($product->getSku())
                        ->setLinkedProductSku($linkProduct->getSku())
                        ->setLinkType($linkType)
                        ->setPosition(isset($linkData['position']) ? (int)$linkData['position'] : 0);
                    $productLinks[] = $link;
                }
            }
        }
        return $product->setProductLinks($productLinks);
    }
	
    /**
     * @param Magento/Catalog/Model/Product $catalogProduct
     * @param array $requestProductData
     *
     * @return array
     */
    private function __productDateTimeFilter($catalogProduct, $requestProductData)
    {
        $dateFieldFilters = [];
        $attributes = $catalogProduct->getAttributes();
        foreach ($attributes as $attrKey => $attribute) {
            if ($attribute->getBackend()->getType() == 'datetime') {
                if (array_key_exists($attrKey, $requestProductData)&&$requestProductData[$attrKey]!='') {
                    $dateFieldFilters[$attrKey] = $this->getDateTimeFilter();
                }
            }
        }
        $inputFilter = new \Zend_Filter_Input(
            $dateFieldFilters,
            [],
            $requestProductData
        );
        $requestProductData = $inputFilter->getUnescaped();
        return $requestProductData;
    }
	
    /**
     * Internal normalization
     * TODO: Remove this method
     *
     * @param array $productData
     * @return array
     */
    protected function normalizeProductData(array $productData)
    {
        foreach ($productData as $key => $value) {
            if (is_scalar($value)) {
                if ($value === 'true') {
                    $productData[$key] = '1';
                } elseif ($value === 'false') {
                    $productData[$key] = '0';
                }
            } elseif (is_array($value)) {
                $productData[$key] = $this->normalizeProductData($value);
            }
        }
        return $productData;
    }
	
	/**
	 * Filter stock data
	 *
	 * @param array $stockData
	 * @return array
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 */
	public function productStockFilter(array $stockData)
	{
		if (!isset($stockData['use_config_manage_stock'])) {
			$stockData['use_config_manage_stock'] = 0;
		}
		if ($stockData['use_config_manage_stock'] == 1 && !isset($stockData['manage_stock'])) {
			$stockData['manage_stock'] = $this->stockConfiguration->getManageStock();
		}
		if (isset($stockData['qty']) && (double)$stockData['qty'] > self::MAX_QTY_VALUE) {
			$stockData['qty'] = self::MAX_QTY_VALUE;
		}
		if (isset($stockData['min_qty']) && (int)$stockData['min_qty'] < 0) {
			$stockData['min_qty'] = 0;
		}
		if (!isset($stockData['is_decimal_divided']) || $stockData['is_qty_decimal'] == 0) {
			$stockData['is_decimal_divided'] = 0;
		}
		return $stockData;
	}
	
    /**
     * @return \Magento\Framework\Stdlib\DateTime\Filter\DateTime
     *
     * @deprecated
     */
    private function getDateTimeFilter()
    {
        if ($this->dateTimeFilter === null) {
            $this->dateTimeFilter = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\Filter\DateTime');
        }
        return $this->dateTimeFilter;
    }	
}