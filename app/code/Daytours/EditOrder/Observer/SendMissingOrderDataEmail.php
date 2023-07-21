<?php

namespace Daytours\EditOrder\Observer;

use Magento\Framework\Event\ObserverInterface;

class SendMissingOrderDataEmail implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderModel;

    /**
     * @var \Daytours\EditOrder\Helper\OrderSender
     */
    protected $orderSender;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderModel
     * @param \Daytours\EditOrder\Helper\OrderSender $orderSender
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderModel,
        \Daytours\EditOrder\Helper\OrderSender $orderSender
    )
    {
        $this->orderModel = $orderModel;
        $this->orderSender = $orderSender;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        if (count($orderIds)) {
            $order = $this->orderModel->create()->load($orderIds[0]);
            $this->orderSender->sendMissingDataOrderEmailAfterOrder($order);
        }
    }
}