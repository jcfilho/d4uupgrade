<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Product\Configurable\Config;

use Magento\Catalog\Model\Product;

class Matrix extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $_configurableType;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix
     */
    protected $variationMatrix;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /** @var \Magento\Catalog\Helper\Image */
    protected $image;

    /** @var null|array */
    private $productMatrix;

    /** @var null|array */
    private $productAttributes;
	
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;	

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param \Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix $variationMatrix
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Helper\Image $image
	 * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
	 * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix $variationMatrix,
		\Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\Image $image,
		\Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
		\Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_configurableType = $configurableType;
        $this->stockRegistry = $stockRegistry;
        $this->variationMatrix = $variationMatrix;
        $this->productRepository = $productRepository;
        $this->image = $image;
        $this->_registry = $registry;
    }

    /**
     * Retrieve currently edited product object
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->_registry->registry('product');
    }

    /**
     * Retrieve attributes data
     *
     * @return array
     */
    protected function getProAttributes()
    {
        if (!$this->hasData('attributes')) {
            $attributes = (array)$this->_configurableType->getConfigurableAttributesAsArray($this->getProduct());
            $productData = (array)$this->getRequest()->getParam('product');
            if (isset($productData['configurable_attributes_data'])) {
                $configurableData = $productData['configurable_attributes_data'];
                foreach ($attributes as $key => $attribute) {
                    if (isset($configurableData[$key])) {
                        $attributes[$key] = array_replace_recursive($attribute, $configurableData[$key]);
                        $attributes[$key]['values'] = array_merge(
                            isset($attribute['values']) ? $attribute['values'] : [],
                            isset($configurableData[$key]['values'])
                            ? array_filter($configurableData[$key]['values'])
                            : []
                        );
                    }
                }
            }
            $this->setData('attributes', $attributes);
        }
        return $this->getData('attributes');
    }

    /**
     * Get used product attributes
     *
     * @return array
     */
    protected function getProConfigUsedAttributes()
    {
        return $this->_configurableType->getUsedProductAttributes($this->getProduct());
    }

    /**
     *
     * @return Product[]
     */
    protected function getAssocProducts()
    {
        $productByUsedAttributes = [];
        foreach ($this->_getAssocProducts() as $product) {
            $keys = [];
            foreach ($this->getProConfigUsedAttributes() as $attribute) {
                /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
                $keys[] = $product->getData($attribute->getAttributeCode());
            }
            $productByUsedAttributes[implode('-', $keys)] = $product;
        }
        return $productByUsedAttributes;
    }

    /**
     * Retrieve actual list of associated products (i.e. if product contains variations matrix form data
     * - previously saved in database relations are not considered)
     *
     * @return Product[]
     */
    protected function _getAssocProducts()
    {
        $product = $this->getProduct();
        $ids = $this->getProduct()->getAssociatedProductIds();
        if ($ids === null) {
            // form data overrides any relations stored in database
            return $this->_configurableType->getUsedProducts($product);
        }
        $products = [];
        foreach ($ids as $productId) {
            try {
                $products[] = $this->productRepository->getById($productId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                continue;
            }
        }
        return $products;
    }

    /**
     * @param Product $product
     * @return float
     */
    public function getProConfigStockQty(Product $product)
    {
        return $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId())->getQty();
    }

    /**
     * @param array $initData
     * @return string
     */
    public function getProConfigVariationWizard($initData)
    {
        /** @var \Magento\Ui\Block\Component\StepsWizard $wizardBlock */
        $wizardBlock = $this->getChildBlock('variation-steps-wizard');
        if ($wizardBlock) {
            $wizardBlock->setInitData($initData);
            return $wizardBlock->toHtml();
        }
        return '';
    }

    /**
     * @return array|null
     */
    public function getProConfigMatrix()
    {
        if ($this->productMatrix === null) {
            $this->prepareProductConfigVariations();
        }
        return $this->productMatrix;
    }

    /**
     * @return array|null
     */
    public function getProConfigAttributes()
    {
        if ($this->productAttributes === null) {
            $this->prepareProductConfigVariations();
        }
        return $this->productAttributes;
    }

    /**
     * @return void
     * TODO: move to class
     */
    protected function prepareProductConfigVariations()
    {
        $variations = $this->variationMatrix->getVariations($this->getProAttributes());
        $productMatrix = [];
        $attributes = [];
        if ($variations) {
            $usedProductAttributes = $this->getProConfigUsedAttributes();
            $productByUsedAttributes = $this->getAssocProducts();
            $configurableAttributes = $this->getProAttributes();
            foreach ($variations as $variation) {
                $attributeValues = [];
                foreach ($usedProductAttributes as $attribute) {
                    $attributeValues[$attribute->getAttributeCode()] = $variation[$attribute->getId()]['value'];
                }
                $key = implode('-', $attributeValues);
                if (isset($productByUsedAttributes[$key])) {
                    $product = $productByUsedAttributes[$key];
                    $price = $product->getPrice();
                    $variationOptions = [];
                    foreach ($usedProductAttributes as $attribute) {
                        if (!isset($attributes[$attribute->getAttributeId()])) {
                            $attributes[$attribute->getAttributeId()] = [
                                'code' => $attribute->getAttributeCode(),
                                'label' => $attribute->getStoreLabel(),
                                'id' => $attribute->getAttributeId(),
                                'position' => $configurableAttributes[$attribute->getAttributeId()]['position'],
                                'chosen' => [],
                            ];
                            foreach ($attribute->getOptions() as $option) {
                                if (!empty($option->getValue())) {
                                    $attributes[$attribute->getAttributeId()]['options'][] = [
                                        'attribute_code' => $attribute->getAttributeCode(),
                                        'attribute_label' => $attribute->getStoreLabel(0),
                                        'id' => $option->getValue(),
                                        'label' => $option->getLabel(),
                                        'value' => $option->getValue(),
                                    ];
                                }
                            }
                        }
                        $optionId = $variation[$attribute->getId()]['value'];
                        $variationOption = [
                            'attribute_code' => $attribute->getAttributeCode(),
                            'attribute_label' => $attribute->getStoreLabel(0),
                            'id' => $optionId,
                            'label' => $variation[$attribute->getId()]['label'],
                            'value' => $optionId,
                        ];
                        $variationOptions[] = $variationOption;
                        $attributes[$attribute->getAttributeId()]['chosen'][] = $variationOption;
                    }

                    $productMatrix[] = [
                        'productId' => $product->getId(),
                        'images' => [
                            'preview' => $this->image->init($product, 'product_thumbnail_image')->getUrl()
                        ],
                        'sku' => $product->getSku(),
                        'name' => $product->getName(),
                        'quantity' => $this->getProConfigStockQty($product),
                        'price' => $price,
                        'options' => $variationOptions,
                        'weight' => $product->getWeight(),
                        'status' => $product->getStatus()
                    ];
                }
            }
        }
        $this->productMatrix = $productMatrix;
        $this->productAttributes = array_values($attributes);
    }
}
