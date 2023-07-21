<?php

namespace Daytours\PayPalButtons\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\OrderRepository;

class SuccessAction implements ObserverInterface
{

    /**
     * @var OrderInterface
     */
    protected $order;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    public function __construct(
        OrderInterface $order,
        OrderFactory $orderFactory,
        ScopeConfigInterface $scopeConfig,
        OrderRepository $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->order = $order;
        $this->orderFactory = $orderFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderId = $observer->getEvent()->getOrderIds()[0];
        $order = $this->orderFactory->create()->load($orderId);
        if (strcmp($order->getPayment()->getMethod(), "paypal_buttons") === 0) {
            $order->setTotalPaid($order->getGrandTotal());
            $this->orderRepository->save($order);
        }
    }
}
