<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Product;

class AddProduct extends \Magento\Framework\View\Element\Template{
	
    /**
     * Fixed bundle price type
     */
    const PRICE_TYPE_FIXED = 1;
	
    /**
     * Dynamic bundle price type
     */
    const PRICE_TYPE_DYNAMIC = 0;
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

	protected $_visibility;
	
	protected $_magebayData;
	
	protected $_product;
	
	protected $_weightResolver;
	
	protected $_coreRegistry = null;
	
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;
    protected $_resource;
	
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magebay\Marketplace\Helper\Data $magebayData,
		\Magento\Catalog\Model\Product\Visibility $visibility,	
		\Magento\Catalog\Model\Product\Edit\WeightResolver $weightResolver,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\Registry $coreRegistry,
		\Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Framework\App\ResourceConnection $resource,
		array $data = []
	) {
        $this->_visibility = $visibility;                
        $this->_magebayData = $magebayData;   
        $this->_weightResolver = $weightResolver;
		$this->_objectManager = $objectManager;
		$this->_coreRegistry = $coreRegistry;
		$this->_wysiwygConfig = $wysiwygConfig;
        $this->_resource = $resource;
		parent::__construct($context, $data);
	}
	
	public function getActionForm(){
		$_param=array();
		$set=self::getParamSet();
		if($set)
			$_param['set']=$set;
		$type=$this->getRequest()->getParam('type');
		if($type)
			$_param['type']=$type;
		$productId=$this->getRequest()->getParam('id');
		if($productId)
			$_param['id']=$productId;
		$url=$this->getUrl('marketplace/product/save',$_param);
		return $url;
	}
	
    public function getWysiwygConfig()
    {
        //$config = $this->_wysiwygConfig->getConfig()->getData();
        $config['content_css'] = [$this->_assetRepo->getUrl(
			'Magebay_Marketplace/css/content.css'
		)];
        $config = json_encode($config);
		return $config;
    }	
	
	public function getGroupProduct(){
		$set=self::getParamSet();
	}
	
	public function getOptionVisibility(){	
		return $this->_visibility->getOptionArray();
	}
    
	public function getParamSet(){
		return (int)$this->getRequest()->getParam('set');
	}
    
	public function getOptionSetGroup(){
		$set=self::getParamSet();
		$setOptionArray = $this->_magebayData->getOptionSetGroup();
		$resultArray=array();
		$_resultArray=array();
		foreach($setOptionArray as $k=>$v){
			$_resultArray[]=array('value'=>$v['value'],'label'=>$v['label']);
		}	
		return $_resultArray;
	}
	
	public function getProduct(){
		return $this->_coreRegistry->registry('current_product');
	}

	public function getProductTypeId(){
		if($typeid=$this->getProduct()->getTypeId()){
			return $typeid;
		}else{
			return $this->getRequest()->getParam('type');
		}
	}

	public function formatDateTime($date){
		if(!$date) return ;
		$date = new \DateTime($date);
		return $date->format('m/d/Y');
	}
	
	public function getQtyProduct($productid,$storeid){
		if(!$productid) return 1;
		$connection=$this->_objectManager->create('Magento\CatalogInventory\Model\ResourceModel\Stock\Status')->getConnection();
        $select = $connection->select()
            ->from($this->_resource->getTableName('cataloginventory_stock_status'), ['product_id', 'qty'])
            ->where('product_id IN(?)', $productid)
            ->where('stock_id=?', (int) \Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID)
            ->where('website_id=?', (int) $storeid);
		return $connection->fetchPairs($select);			
	}
	
	public function getProductSimpleOfProductConfig($productid){				
		return $this->_objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getChildrenIds($productid);
	}
    
    /**
	 * @param \Magento\Catalog\Model\Product $product
	 * @return Number
	 */
	public function getBundlePrice(\Magento\Catalog\Model\Product $product) {
        if ($product->getPriceType() == self::PRICE_TYPE_FIXED) {
            return $product->getData('price');
        } else {
            return 0;
        }
	}
	
	/**
	 * @param \Magento\Catalog\Model\Product $product
	 * @return Number
	 */
	public function getBundleFinalPrice(\Magento\Catalog\Model\Product $product) {
		return $product->getFinalPrice();
	}
    
    /**
	 * @param \Magento\Catalog\Model\Product $product
	 * @return boolean
	 */
	public function checkDisablePrice(\Magento\Catalog\Model\Product $product) {
        if ($product->getPriceType() == self::PRICE_TYPE_FIXED) {
            return false;
        } else {
            return true;
        }
	}

	/**
	 * @param \Magento\Catalog\Model\Product $product
	 * @return boolean
	 */
	public function checkDisableWeight(\Magento\Catalog\Model\Product $product) {
        if ($product->getWeightType() == self::PRICE_TYPE_FIXED) {
            return false;
        } else {
            return true;
        }
	}
	
	public function getPriceProduct(\Magento\Catalog\Model\Product $product){
		return $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
	}
	
	public function getFinalPriceProduct(\Magento\Catalog\Model\Product $product){
		return $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
	}
	
	public function checkShowChildHtmlDownload(){
		if($this->getProductTypeId()==\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
			return false;
		}
		return true;
	}
    
    public function productIsGroup() {
		if($this->getProductTypeId()==\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE){
			return true;
		}
		return false;
	}
	
	public function productIsBundle() {
		if($this->getProductTypeId()==\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE){
			return true;
		}
		return false;
	}
	
	public function checkShowChildHtmlConfig() {
		if($this->getProductTypeId()==\Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE){
			return false;
		}
		return true;		
	}
	
	public function getStockStatus($productid,$storeid){
		if(!$productid) return 1;
		$_stock_status=$this->_objectManager->create('Magento\CatalogInventory\Model\ResourceModel\Stock\Status')->getProductsStockStatuses($productid,$storeid);
		return $_stock_status[$productid];
	}
	
	public function checkProductHasWieght(\Magento\Catalog\Model\Product $product){
		return $this->_weightResolver->resolveProductHasWeight($product);
	}
}