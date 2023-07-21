<?php
/**
 * @Author      : Dream
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block;
use Magento\Store\Model\ScopeInterface;
class SellerProducts extends \Magento\Catalog\Block\Product\ListProduct
{
	protected $_resource;
	protected $_product;
	protected $_modelSession;
	protected $_summaryFactory;
    /**
     * @var ReviewRendererInterface
     */
    protected $reviewRenderer;
    
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
        $this->reviewRenderer = $context->getReviewRenderer();
        parent::__construct($context,$postDataHelper,$layerResolver,$categoryRepository,$urlHelper,$data);
    }
	function getSellerProfile()
	{
		$seller = null;
		if($this->_coreRegistry->registry('seller_profile'))
		{
			$seller = $this->_coreRegistry->registry('seller_profile');
		}
		return $seller;
	}
	protected function _getProductCollection()
	{
		$seller = $this->getSellerProfile();
		$collection = null;
		$orderBy = $this->getRequest()->getParam('product_list_order','position');
		$sortOrder = $this->getRequest()->getParam('product_list_dir','ASC');
        $seller_search = $this->getRequest()->getParam('seller_search',null);
        $litmit = $this->getRequest()->getParam('product_list_limit',9);
		$curPage = $this->getRequest()->getParam('p',1);
		if($seller && $seller->getId())
		{
			$customerSession = $this->_modelSession;
			$tableMKproduct = $this->_resource->getTableName('multivendor_product');
			$collection = $this->_product->getCollection();
			$collection->addAttributeToSelect(array('*'));
            $collection->addAttributeToFilter('status',1);
            $collection->addAttributeToFilter('visibility', array('in' => array(2,3,4)));
			if($customerSession->isLoggedIn()){
				
			}else{
				$collection->addAttributeToFilter('status',1);
			}
			$collection->getSelect()->joinLeft(array('mk_product'=>$tableMKproduct),'e.entity_id = mk_product.product_id',array())
					->where('mk_product.user_id=?',$seller->getId())
                    ->where('mk_product.status = 1');                    
			//$collection->addAttributeToSort($orderBy,$sortOrder);
            if($seller_search){
                $collection->addAttributeToFilter('name', array('like' => '%'.$seller_search.'%'));
            }
			if($litmit > 0)
			{
				$collection->setPageSize($litmit);
			}	
			if($curPage > 1)
			{
				$collection->setCurPage($curPage);
			}
		}
		$this->_productCollection = $collection;
		return parent::_getProductCollection();
	}
	function getMKReviewsSummaryHtml($productId)
	{
		$data = array();
		$storeId = $this->_storeManager->getStore()->getId();
		$modelReview = $this->_summaryFactory->create();
		$reviewSummary = $modelReview->setStoreId($storeId)->load($productId);
		if($reviewSummary)
		{
			$data = $reviewSummary->getData();
		}
		return $data;
	}
    public function getReviewsSummaryHtml(
        \Magento\Catalog\Model\Product $product,
        $templateType = false,
        $displayIfNoReviews = false
    ) {
		$templateType = \Magento\Catalog\Block\Product\ReviewRendererInterface::SHORT_VIEW;
        $displayIfNoReviews = true;
        return $this->reviewRenderer->getReviewsSummaryHtml($product, $templateType, $displayIfNoReviews);
    }
	function getMkConfig($field)
	{
		return $this->_scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE);
	}
}