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

class MassUnhold extends \Magebay\Marketplace\Controller\Seller\Order\AbstractMassAction
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
     * Unhold selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countUnHoldOrder = 0;
		$seller = $this->_modelSession;
		$tableSalelist = $this->_resource->getTableName('multivendor_saleslist');
		$collection->getSelect()->joinLeft(
			array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
			array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
		);
		$collection->getSelect()->where('mk_sales_list.sellerid=?', $seller->getId() );
		$collection->getSelect()->group('main_table.entity_id');
		$orders = $collection->getItems();
        /** @var \Magento\Sales\Model\Order $order */
        foreach ( $orders as $order) {
            $order->load($order->getId());
            if (!$order->canUnhold()) {
                continue;
            }
            $order->unhold();
            $order->save();
            $countUnHoldOrder++;
        }
        $countNonUnHoldOrder = $collection->count() - $countUnHoldOrder;
        if ($countNonUnHoldOrder && $countUnHoldOrder) {
            $this->messageManager->addError(
                __('%1 order(s) were not released from on hold status.', $countNonUnHoldOrder)
            );
        } elseif ($countNonUnHoldOrder) {
            $this->messageManager->addError(__('No order(s) were released from on hold status.'));
        }
        if ($countUnHoldOrder) {
            $this->messageManager->addSuccess(
                __('%1 order(s) have been released from on hold status.', $countUnHoldOrder)
            );
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath( 'marketplace/seller/myOrders' );
        return $resultRedirect;
    }
}
