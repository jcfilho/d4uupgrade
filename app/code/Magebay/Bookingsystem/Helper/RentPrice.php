<?php
 
namespace Magebay\Bookingsystem\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magebay\Bookingsystem\Model\CalendarsFactory;

class RentPrice extends AbstractHelper
{
	/**
	* @var Magento\Catalog\Model\Calendars
	**/
	protected $_calendarsFactory;
	
	public function __construct(
       Context $context,
       CalendarsFactory $calendarsFactory
    ) 
	{
       parent::__construct($context);
	   $this->_calendarsFactory = $calendarsFactory;
    }
	/** get price of day
	* @params int $bookingId, string $strDay, $bookingType
	* @return array $price
	**/
	function getPriceOfDay($bookingId,$strDay,$bookingType)
	{
		$calendarModel = $this->_calendarsFactory->create();
		$calendar = $calendarModel->getCalendarBetweenDays($bookingId,$strDay,$bookingType);
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
 