<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block;
use Magento\Store\Model\ScopeInterface;
class Productlist extends \Magento\Catalog\Block\Product\ListProduct {
	protected $_resource;
	protected $_product;
	protected $_modelSession;
	protected $_summaryFactory;
    
	public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Catalog\Model\Product $product,
		\Magento\Customer\Model\Session $modelSession,
		\Magento\Review\Model\Review\SummaryFactory $summaryFactory,
        array $data = []
    ) {
		$this->_resource = $resource;
		$this->_product = $product;
		$this->_modelSession = $modelSession;
		$this->_summaryFactory = $summaryFactory;
        parent::__construct($context,$postDataHelper,$layerResolver,$categoryRepository,$urlHelper,$data);
    }
    
	function getSellerProfile(){
		$seller = null;
		if($this->_coreRegistry->registry('seller_profile')){
			$seller = $this->_coreRegistry->registry('seller_profile');
		}
		return $seller;
	}
    
	protected function _getProductCollection(){
		$seller = $this->_modelSession;
		$collection = null;
		$sortOrder = $this->getRequest()->getParam('product_list_dir','DESC');
		$orderBy = $this->getRequest()->getParam('product_list_order','position');
		$litmit = $this->getRequest()->getParam('limit',5);
		if($seller && $seller->getId()){
			$customerSession = $this->_modelSession;
			$tableMKproduct = $this->_resource->getTableName('multivendor_product');
			$collection = $this->_product->getCollection();
			$collection->addAttributeToSelect(array('*'));
			if($customerSession->isLoggedIn()){
				
			}else{
				$collection->addAttributeToFilter('status',1);
			}
			$collection->getSelect()->joinLeft(array('mk_product'=>$tableMKproduct),'e.entity_id = mk_product.product_id',array('mkproductstatus'=>"mk_product.status"))->where('mk_product.user_id=?',$seller->getId());
			$collection->addAttributeToSort($orderBy,$sortOrder);
			if($litmit > 0){
				$collection->setPageSize($litmit);
			}
			$curPage = $this->getRequest()->getParam('p',1);
			if($curPage > 1)
			{
				$collection->setCurPage($curPage);
			}
		}
		$this->_productCollection = $collection;
		return parent::_getProductCollection();
	}
    
	function getMKReviewsSummaryHtml($productId){
		$data = array();
		$storeId = $this->_storeManager->getStore()->getId();
		$modelReview = $this->_summaryFactory->create();
		$reviewSummary = $modelReview->setStoreId($storeId)->load($productId);
		if($reviewSummary){
			$data = $reviewSummary->getData();
		}
		return $data;
	}
    
	function getMkConfig($field){
		return $this->_scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE);
	}
        
    protected function _prepareLayout()
    {
        $collection = $this->_getProductCollection();
        parent::_prepareLayout();
        if ($collection) {
            // create pager block for collection
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager','my.custom.pager');
            $pager->setAvailableLimit(array(5=>5,10=>10,20=>20,'all'=>'all')); 
            $pager->setCollection($collection);
            $this->setChild('pager', $pager);
            $collection->load();
        }
        return $this;
    }

    /**
     * @return  method for get pager html
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}