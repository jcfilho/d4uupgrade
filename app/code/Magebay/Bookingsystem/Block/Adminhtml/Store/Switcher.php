<?php

namespace Magebay\Bookingsystem\Block\Adminhtml\Store;
 
class Switcher extends \Magento\Backend\Block\Store\Switcher
{
	function getBkItemId()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$request = $objectManager->get('Magento\Framework\App\RequestInterface');
		$id = $request->getParam('id',0);
		return $id;
	}
}