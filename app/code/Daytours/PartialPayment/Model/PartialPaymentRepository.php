<?php

namespace Daytours\PartialPayment\Model;

use Daytours\PartialPayment\Api\PartialPaymentRepositoryInterface;
use Exception;
use PhpParser\Node\Stmt\TryCatch;

/**
 * Factory class for @see \Daytours\PartialPayment\Model\PartialPaymentRepository
 */
class PartialPaymentRepository implements PartialPaymentRepositoryInterface
{

    protected $_orderCollectionFactory;

    protected $order;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\Order $order
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->order = $order;
    }

    public function getOrderById($orderId)
    {
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', ['eq' => $orderId])
            ->addFieldToFilter('base_total_due', ['neq' => '0']);
        return $collection->toArray();
    }

    public function payDueAmount($protectId)
    {
        try {
            $collection = $this->_orderCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('base_total_due', ['neq' => '0']);
            //->addFieldToFilter('protect_code', ['eq' => $protectId]);
            $orderDataItem = $collection->getItemByColumnValue("protect_code", $protectId);
            if ($orderDataItem) {
                $orderId = $orderDataItem["entity_id"];
                $order = $this->order->load($orderId);
                $order->setBaseTotalPaid($order->getBaseGrandTotal());
                $order->setBaseTotalDue(0);
                $order->setTotalPaid($order->getGrandTotal());
                $order->setTotalDue(0);
                $order->save();
                return json_encode(array(
                    "message" => "Payment has been recorder successfully",
                    "status" => "200"
                ));
            }
            else{
                return json_encode(array(
                    "message" => "Order not found",
                    "status" => "404"
                ));
            }
            //var_dump($collection->getItems());
            //return $orderDataItem->toJson();//$collection->getItems();
        } catch (Exception $e) {
            return json_encode(array(
                "message" => $e->getMessage(),
                "status" => "500"
            ));
        }
    }
}
