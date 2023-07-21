<?php
 
namespace Daytours\Bookingsystem\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magebay\Bookingsystem\Model\CalendarsFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Backend\Helper\Data as BackendHelper;

class RentPrice extends \Magebay\Bookingsystem\Helper\RentPrice
{
	/**
	* @var \Magento\Catalog\Model\Calendars
	**/
	protected $_calendarsFactory;
	
	public function __construct(
       Context $context,
       CalendarsFactory $calendarsFactory
    ) 
	{
       parent::__construct($context,$calendarsFactory);
	   $this->_calendarsFactory = $calendarsFactory;
    }
	/** get price of day
	* @params int $bookingId, string $strDay, $bookingType
	* @return array $price
	**/
	function getPriceOfDay($bookingId,$strDay,$bookingType,$calendarNumber = 1)
	{
		$calendarModel = $this->_calendarsFactory->create();
		$calendar = $calendarModel->getCalendarBetweenDays($bookingId,$strDay,$bookingType,$calendarNumber);
		$prices = array(
			'price'=> 0,
			'promo'=> 0
			);
		if($calendar->getId())
		{
			$prices = array(
				'price'=> $calendar->getCalendarPrice(),
				'promo'=> $calendar->getCalendarPromo(),
			);
		}
		return $prices;
	}
	
}
 