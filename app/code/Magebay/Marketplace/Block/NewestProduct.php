<?php
/**
 * @Author      : Dream
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block;
class NewestProduct extends \Magento\Catalog\Block\Product\ListProduct
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
		\Magento\Review\Model\Review\SummaryFactory $summaryFactory,
        array $data = []
    ) {
		$this->_resource = $resource;
		$this->_product = $product;
		$this->_summaryFactory = $summaryFactory;
        $this->reviewRenderer = $context->getReviewRenderer();
        parent::__construct($context,$postDataHelper,$layerResolver,$categoryRepository,$urlHelper,$data);
    }
	protected function _getProductCollection()
	{
		$collection = null;
		$limit = $this->getRequest()->getParam('product_list_limit',9);
		$orderBy = $this->getRequest()->getParam('product_list_order','position');
		$sortOrder = $this->getRequest()->getParam('product_list_dir','ASC');
		$curPage = $this->getRequest()->getParam('p',1);
		$tableMKproduct = $this->_resource->getTableName('multivendor_product');
		$collection = $this->_product->getCollection();
		$collection->addAttributeToSelect(array('*'));
		$collection->addAttributeToFilter('status',1);
        $collection->addAttributeToFilter('visibility', array('in' => array(2,3,4)));
		$collection->getSelect()->joinLeft(array('mk_product'=>$tableMKproduct),'e.entity_id = mk_product.product_id',array())
				->where('mk_product.user_id IS NOT NULL')
                ->where('mk_product.status = 1');
        //Kien 19/5/2016 - update filter seller approve        
        $tableMKuser = $this->_resource->getTableName('multivendor_user');
        $collection->getSelect()->joinLeft(array('mk_user'=>$tableMKuser),'mk_product.user_id = mk_user.user_id',array())
                ->where('mk_user.userstatus = 1');
                
		//$collection->addAttributeToSort($orderBy,$sortOrder);
		if($limit > 0)
		{
			$collection->setPageSize($limit);
		}
		if($curPage > 1)
		{
			$collection->setCurPage($curPage);
		}
		$this->_productCollection = $collection;
		return parent::_getProductCollection();
	}
	public function getLoadedProductCollection()
    {
        return $this->_getProductCollection();
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
}