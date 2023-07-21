<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebay\Marketplace\Controller\Seller\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class MassCancel extends \Magebay\Marketplace\Controller\Seller\Order\AbstractMassAction
{
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
	protected $_resource;
	protected $_modelSession;
	
    public function __construct(
    	Context $context, 
    	Filter $filter, 
    	CollectionFactory $collectionFactory,
    	\Magento\Framework\App\ResourceConnection $resource,
    	\Magento\Customer\Model\Session $modelSession
	)
    {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
		$this->_resource = $resource;
		$this->_modelSession = $modelSession;
    }

    /**
     * Cancel selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countCancelOrder = 0;
		$seller = $this->_modelSession;
		$tableSalelist = $this->_resource->getTableName('multivendor_saleslist');
		$collection->getSelect()->joinLeft(
			array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
			array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
		);
		$collection->getSelect()->where('mk_sales_list.sellerid=?', $seller->getId() );
		$collection->getSelect()->group('main_table.entity_id');
		$orders = $collection->getItems();
        foreach ($orders as $order) {
            if (!$order->canCancel()) {
                continue;
            }
            $order->cancel();
            $order->save();
            $countCancelOrder++;
        }
        $countNonCancelOrder = $collection->count() - $countCancelOrder;
        if ($countNonCancelOrder && $countCancelOrder) {
            $this->messageManager->addError(__('%1 order(s) cannot be canceled.', $countNonCancelOrder));
        } elseif ($countNonCancelOrder) {
            $this->messageManager->addError(__('You cannot cancel the order(s).'));
        }
        if ($countCancelOrder) {
            $this->messageManager->addSuccess(__('We canceled %1 order(s).', $countCancelOrder));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        //$resultRedirect->setPath($this->getComponentRefererUrl());
        $resultRedirect->setPath( 'marketplace/seller/myOrders' );
        return $resultRedirect;
    }
}