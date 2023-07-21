<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Product;

use Magento\Framework\Controller\ResultFactory;
use Magebay\Marketplace\Controller\Product\Builder;
use Magebay\Marketplace\Controller\Product\Action\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magebay\Marketplace\Model\ProductsFactory;

class MassDeleteUi extends \Magebay\Marketplace\Controller\Product\Product
{
	const PRODUCT_FIELD_ID='entity_id';
	const PRODUCT_MARKET_FIELD_ID='product_id';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    protected $productsFactory;
	protected $filter;
	protected $_resource;
	protected $_modelSession;
	protected $_productIds;

    /**
     * @param Context $context
     * @param Builder $productBuilder
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Builder $productBuilder,        
		ProductsFactory $productsFactory,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Customer\Model\Session $modelSession,
		Filter $filter,
        CollectionFactory $collectionFactory
    ) {        
        $this->collectionFactory = $collectionFactory;
        $this->productsFactory = $productsFactory;		
		$this->filter = $filter;
		$this->_resource = $resource;
		$this->_modelSession = $modelSession;
        parent::__construct($context, $productBuilder);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {		
		$registry=$this->_objectManager->get('Magento\Framework\Registry');				
		$registry->register('isSecureArea', true);
		$collection = $this->filter->getCollection($this->collectionFactory->create());
		
		$tableMKproduct = $this->_resource->getTableName('multivendor_product');
		$seller = $this->_modelSession;
		$collection->getSelect()->joinLeft(array('mk_product'=>$tableMKproduct),'e.entity_id = mk_product.product_id',array())
					->where('mk_product.user_id=?',$seller->getId());
		
		$this->_productIds = $collection->getAllIds();
        
        $productDeleted = 0;				
        foreach ($collection->getItems() as $product) {				
            $product->delete();			
            $productDeleted++;
        }
		self::deleteProductMarketCollection();
		$registry->unregister('isSecureArea');
        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been deleted.', $productDeleted)
        );
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('marketplace/seller/myProducts');
    }

	protected function deleteProductMarketCollection(){
		$ids= $this->_productIds;		
		foreach($ids as $key=>$id){
			$this->productsFactory->create()
			->load($id,'product_id')
			->delete();			
		}
	}	
}
