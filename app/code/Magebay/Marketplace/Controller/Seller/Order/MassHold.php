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
use Magento\Sales\Api\OrderManagementInterface;

/**
 * Class MassHold
 */
class MassHold extends \Magebay\Marketplace\Controller\Seller\Order\AbstractMassAction
{
    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;
	protected $_resource;
	protected $_modelSession;
	
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderManagementInterface $orderManagement,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Customer\Model\Session $modelSession
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
		$this->_resource = $resource;
		$this->_modelSession = $modelSession;
    }

    /**
     * Hold selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countHoldOrder = 0;
		$seller = $this->_modelSession;
		$tableSalelist = $this->_resource->getTableName('multivendor_saleslist');
		$collection->getSelect()->joinLeft(
			array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
			array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
		);
		$collection->getSelect()->where('mk_sales_list.sellerid=?', $seller->getId() );
		$collection->getSelect()->group('main_table.entity_id');
		$orders = $collection->getItems();
        foreach ( $orders as $order) {
            if (!$order->canHold()) {
                continue;
            }
            $this->orderManagement->hold($order->getEntityId());
            $countHoldOrder++;
        }
        $countNonHoldOrder = $collection->count() - $countHoldOrder;
        if ($countNonHoldOrder && $countHoldOrder) {
            $this->messageManager->addError(__('%1 order(s) were not put on hold.', $countNonHoldOrder));
        } elseif ($countNonHoldOrder) {
            $this->messageManager->addError(__('No order(s) were put on hold.'));
        }
        if ($countHoldOrder) {
            $this->messageManager->addSuccess(__('You have put %1 order(s) on hold.', $countHoldOrder));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath( 'marketplace/seller/myOrders' );
        return $resultRedirect;
    }
}
