<?php

namespace Magebay\Bookingsystem\Observer\Frontend;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class Poadpagebefore implements ObserverInterface
{
	/* 
	* test event load page before
	*/
    public function execute(EventObserver $observer)
    {
		$controllerAction = $observer->getControllerAction();
		// echo $controllerAction;
		$request = $observer->getRequest()->getFullActionName();
    }
}
