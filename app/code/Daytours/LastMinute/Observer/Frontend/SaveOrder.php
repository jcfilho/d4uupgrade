<?php

namespace Daytours\LastMinute\Observer\Frontend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Daytours\LastMinute\Model\Order as LastMinuteOrder;

class SaveOrder implements ObserverInterface
{
    /**
     * @var \Daytours\LastMinute\Model\Order
     **/
    protected $lastMinuteOrder;

    public function __construct(
        LastMinuteOrder $lastMinuteOrder
    )
    {
        $this->lastMinuteOrder = $lastMinuteOrder;
    }

    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($this->lastMinuteOrder->isLastMinute($order)) {
            $order->setIsLastminute(1);
            $order->save();
        }

        return $this;
    }
}
