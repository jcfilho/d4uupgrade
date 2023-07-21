<?php

namespace Magebay\Bookingsystem\Observer\Adminhtml;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magebay\Bookingsystem\Helper\BkHelperDate;
use Magebay\Bookingsystem\Helper\BkCustomOptions;

class AddProductAfter implements ObserverInterface
{
	/**
	* @var Magento\Framework\App\RequestInterface;
	**/
	protected $_request;
	/**
	* @var Magebay\Bookingsystem\Helper\BkHelperDate;
	**/
	protected $_bkHelperDate;
	/**
	* @var Magebay\Bookingsystem\Helper\BkCustomOptions;
	**/
	protected $_bkCustomOptions;
	
	public function __construct(
				RequestInterface $request,
				BkHelperDate $bkHelperDate,
				BkCustomOptions $bkCustomOptions
			)
    {
        $this->_request = $request;
        $this->_bkHelperDate = $bkHelperDate;
        $this->_bkCustomOptions = $bkCustomOptions;
    }
	/* Magento\Quote\Model\Quote.php */
    public function execute(EventObserver $observer)
    {
		$items = $observer->getItems();
		$enable = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/enable');
		if($enable == 1)
		{
			$params = $this->_getBkRequest()->getParams();
			foreach($items as $item)
			{
				//code bk
				$product = $item->getProduct();
				if($product && $product->getTypeId() == 'booking')
				{
					$buyRequest = isset($params['item'][$item->getId()]) ? $params['item'][$item->getId()] : array();
					if(count($buyRequest))
					{
						$dataRequest = serialize($buyRequest);
						$item->addOption(array('code'=> 'info_buyRequest', 'product_id'=> $item->getProductId(), 'value'=> $dataRequest));
						/* $helperCeaateOptions = $this->_bkCustomOptions;
						$bkData = $this->_bkCustomOptions->createExtractOptions($product,$buyRequest);
						if($bkData['status'] == true)
						{
							$additionalOptions = $bkData['bk_options'];
							$dataAdditionalOptions = serialize($additionalOptions);
							$item->addOption(array('code'=> 'additional_options', 'product_id'=> $item->getProductId(), 'value'=> $dataRequest));
						} */
					}
				}
				//code bk
			}
		}
		return $this;
    }
	protected function _getBkRequest()
	{
		return $this->_request;
	}
}
