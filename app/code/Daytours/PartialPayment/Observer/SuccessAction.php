<?php

namespace Daytours\PartialPayment\Observer;

//use Daytours\PartialPayment\Model\PayPalManager;

use Exception;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\OrderRepository;
use PhpParser\Node\Stmt\TryCatch;

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
        try{
            //$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $orderId = $observer->getEvent()->getOrderIds()[0];
            $order = $this->orderFactory->create()->load($orderId);
            $items = $order->getAllVisibleItems();
            $baseDueAmount = 0;
            $basePaidAmount = 0;
            $dueAmount = 0;
            $paidAmount = 0;
            foreach($items as $item){
                $basePrice = $item->getBasePrice();
                $basePaidAmount += $basePrice;
                $price = $item->getPrice();
                $paidAmount += $price;
                $options = $item->getProductOptions();
                if(!empty($options["options"]))
                foreach($options["options"] as $option){
                    if($option["label"] == "Pay Partially"){
                        $percentDiscount = floatval(preg_replace('/\D/', '', $option["value"]))/100;
                        $originalBasePrice = $basePrice/$percentDiscount;
                        $baseDueAmount += $originalBasePrice - $basePrice; 
                        $originalPrice = $price/$percentDiscount;
                        $dueAmount += $originalPrice - $price; 
                    }
                }
            }
            $order->setBaseGrandTotal($basePaidAmount+$baseDueAmount)->setBaseTotalDue($baseDueAmount)->setBaseTotalPaid($basePaidAmount);
            $order->setGrandTotal($paidAmount+$dueAmount)->setTotalDue($dueAmount)->setTotalPaid($paidAmount);
            $this->orderRepository->save($order);
        }
        catch(Exception $err){
            var_dump($err->getMessage());
            exit();
        }
    }
}
