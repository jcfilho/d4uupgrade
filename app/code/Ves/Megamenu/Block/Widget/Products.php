<?php
/**
 * Venustheme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Venustheme
 * @package    Ves_Megamenu
 * @copyright  Copyright (c) 2016 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Megamenu\Block\Widget;

class Products extends \Magento\Catalog\Block\Product\AbstractProduct implements \Magento\Widget\Block\BlockInterface
{
	/**
     * @var \Ves\Megamenu\Model\Product
     */
	protected $_productModel;

	/**
	 * @var \Magento\Catalog\Model\Product
	 */
	protected $_collection;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
	protected $httpContext;

    /**
     * Json Serializer Instance
     *
     * @var Json
     */
    private $json;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Block\Product\Context $context
	 * @param \Ves\Megamenu\Model\Product $productModel
	 * @param \Magento\Framework\App\Http\Context $httpContext
     * @param Json $json = null
	 * @param array $data = []
     */
	public function __construct(
		\Magento\Catalog\Block\Product\Context $context,
		\Ves\Megamenu\Model\Product $productModel,
		\Magento\Framework\App\Http\Context $httpContext,
		array $data = []
		) {
		$this->_productModel = $productModel;
		$this->httpContext = $httpContext;
		parent::__construct($context, $data );
	}

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->addData(
            [
                'cache_lifetime' => false,
                'cache_tags' => [\Ves\Megamenu\Model\Menu::CACHE_WIDGET_PRODUCTS_TAG, \Magento\Store\Model\Group::CACHE_TAG],
            ]
        );
    }

    /**
     * @inheritdoc
     */
	protected function _beforeToHtml()
	{
		$catIds = [];
		$categories = $this->getConfig("categories");
		if($categories!=''){
			$catIds = explode(",", $categories);
		}
		$layoutType = $this->getConfig("layout_type");
		if($layoutType == 'owl_carousel'){
			$this->setTemplate('widget/product_carousel.phtml');
		}elseif($layoutType == 'bootstrap_carousel'){
			$this->setTemplate('widget/bootstrapcarousel.phtml');
		}
		$source_key = $this->getConfig("product_source");
		$config = [];
		$config['pagesize'] = $this->getConfig('number_item',12);
		$config['cats'] = $catIds;
		$collection = $this->_productModel->getProductBySource($source_key, $config);

		$this->_collection = $collection;
		return parent::_beforeToHtml();
	}

    /**
     * get megamenu key
     *
     * @param string $megamenu_key
     * @return string
     */
    public function getMegamenuKey($megamenu_key)
    {
        $params = $this->getRequest()->getParams();
        $params_key = "";
        if ($params) {
            $params_key = array_values($params);
            $params_key = $params_key ? implode('|', $params_key) : "";
            $params_key = $params_key ? ("-". md5($params_key) ) : "";
        }
        return $megamenu_key.$params_key;
    }

	/**
     * Get key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $catIds = [];
		$categories = $this->getConfig("categories");
		if ($categories != '') {
			$catIds = explode(",", $categories);
		}
        $megamenu_key = $catIds ? implode("-", $catIds) : "";
        $megamenu_key .= "-".$this->getConfig("number_item")."-".$this->getConfig("product_source");

        $shortCacheId = [
            'VES_MEGAMENU_PRODUCT',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(Context::CONTEXT_GROUP),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            $megamenu_key
        ];
        $cacheId = $shortCacheId;

        $shortCacheId = array_values($shortCacheId);
        $shortCacheId = implode('|', $shortCacheId);
        $shortCacheId = md5($shortCacheId);

        $cacheId['megamenu_products'] = $megamenu_key;
        $cacheId['short_cache_id'] = $shortCacheId;

        return $cacheId;
    }

    /**
     * Get config
     *
     * @param string $key
     * @param mixed|string|int|null $default
     * @return mixed|string|int|null
     */
	public function getConfig($key, $default = '')
	{
		if($this->hasData($key) && $this->getData($key))
		{
			return $this->getData($key);
		}
		return $default;
	}

	/**
     * Check product is new
     *
     * @param mixed|object|null $_product
     * @return bool
     */
	public function checkProductIsNew($_product = null)
    {
		$from_date = $_product->getNewsFromDate();
		$to_date = $_product->getNewsToDate();
		$is_new = false;
		$is_new = $this->isNewProduct($from_date, $to_date);
		$today = strtotime("now");

		if ($from_date && $to_date) {
			$from_date = strtotime($from_date);
			$to_date = strtotime($to_date);
			if ($from_date <= $today && $to_date >= $today) {
				$is_new = true;
			}
		} elseif ($from_date && !$to_date) {
			$from_date = strtotime($from_date);
			if ($from_date <= $today) {
				$is_new = true;
			}
		} elseif (!$from_date && $to_date) {
			$to_date = strtotime($to_date);
			if ($to_date >= $today) {
				$is_new = true;
			}
		}
		return $is_new;
	}

    /**
     * Get product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|mixed
     */
	public function getProductCollection()
    {
		return $this->_collection;
	}

    /**
     * Check product is new by date
     *
     * @param string $created_date
     * @param int $num_days_new
     * @return bool
     */
	public function isNewProduct( $created_date, $num_days_new = 3)
    {
		$check = false;

		$startTimeStamp = strtotime($created_date);
		$endTimeStamp = strtotime("now");

		$timeDiff = abs($endTimeStamp - $startTimeStamp);
        $numberDays = $timeDiff/86400;// 86400 seconds in one day

        // and you might want to convert to integer
        $numberDays = intval($numberDays);
        if ($numberDays <= $num_days_new) {
        	$check = true;
        }

        return $check;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getVesProductPriceHtml(
    	\Magento\Catalog\Model\Product $product,
    	$priceType = null,
    	$renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
    	array $arguments = []
    	)
    {
    	if (!isset($arguments['zone'])) {
    		$arguments['zone'] = $renderZone;
    	}
    	$arguments['price_id'] = isset($arguments['price_id'])
    	? $arguments['price_id']
    	: 'old-price-' . $product->getId() . '-' . $priceType;
    	$arguments['include_container'] = isset($arguments['include_container'])
    	? $arguments['include_container']
    	: true;
    	$arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
    	? $arguments['display_minimal_price']
    	: true;
    	$priceRender = $this->getLayout()->getBlock('product.price.render.default');

    	$price = '';
    	if ($priceRender) {
    		$price = $priceRender->render(
    			\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
    			$product,
    			$arguments
    			);
    	}
    	return $price;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        return [\Ves\Megamenu\Model\Menu::CACHE_WIDGET_PRODUCTS_TAG, \Magento\Store\Model\Group::CACHE_TAG];
    }
}
