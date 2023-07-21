<?php
namespace Magebay\Bookingsystem\Plugin\Sales\Model;

use Magento\Checkout\Model\Cart as BkCoreCart;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Message\ManagerInterface;
use Magebay\Bookingsystem\Model\BookingsFactory;
use Magebay\Bookingsystem\Model\RoomsFactory;
use Magebay\Bookingsystem\Helper\BkHelperDate;
use Magebay\Bookingsystem\Helper\BkCustomOptions;
use Magebay\Bookingsystem\Helper\BkSimplePriceHelper;
use Magebay\Bookingsystem\Helper\IntervalsPrice;
use Magebay\Bookingsystem\Helper\RoomPrice;

class Order
{
	/**
	* @var BkCoreCart;
	**/
	protected $_bkCoreCart;
	/**
	* @var \Magento\Framework\Pricing\Helper\Data;
	**/
	protected $_bkPriceHelper;
	/**
	* @var DirectoryHelper
	**/
	protected $_directoryHelper;
	/**
	* @var DirectoryHelper
	**/
	protected $_messageManager;
	/**
	* @var \Magebay\Bookingsystem\Model\BookingsFactory;
	**/
	
	protected $_bookingFactory;
	/**
     * @var \Magebay\Bookingsystem\Model\RoomsFactory
    */
	protected $_roomsFactory;
	/**
	* @var \Magebay\Bookingsystem\Helper\BkHelperDate;
	**/
	protected $_bkHelperDate;
	/**
	* @var Magebay\Bookingsystem\Helper\BkCustomOptions;
	**/
	protected $_bkCustomOptions;
	/**
	* @var \Magebay\Bookingsystem\Helper\BkSimplePriceHelper;
	**/
	protected $_bkSimplePriceHelper;
	/**
	* @var \Magebay\Bookingsystem\Helper\IntervalsPrice;
	**/
	protected $_intervalsPrice;
	/**
	* @var \Magebay\Bookingsystem\Helper\RoomPrice;
	**/
	protected $_roomPrice;
	public function __construct(
		BkCoreCart $bkCoreCart,
		PriceHelper $bkPriceHelper,
		DirectoryHelper $directoryHelper,
		ManagerInterface $messageManager,
		BookingsFactory $bookingFactory,
		RoomsFactory $roomsFactory,
		BkHelperDate $bkHelperDate,
		BkCustomOptions $bkCustomOptions,
		BkSimplePriceHelper $bkSimplePriceHelper,
		IntervalsPrice $intervalsPrice,
		RoomPrice $roomPrice
		
	)
    {
		$this->_bkCoreCart = $bkCoreCart;
		$this->_bkPriceHelper = $bkPriceHelper;
		$this->_directoryHelper = $directoryHelper;
		$this->_messageManager = $messageManager;
		$this->_bookingFactory = $bookingFactory;
		$this->_roomsFactory = $roomsFactory;
		$this->_bkHelperDate = $bkHelperDate;
		$this->_bkCustomOptions = $bkCustomOptions;
		$this->_bkSimplePriceHelper = $bkSimplePriceHelper;
		$this->_intervalsPrice = $intervalsPrice;
		$this->_roomPrice = $roomPrice;
    }
	public function beforePlace($subject)
    {

		$enable = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/enable');
		if($enable == 1)
		{
			$bkError = false;
			$carts = $this->_bkCoreCart;
			if ($carts->getQuote()->getItemsCount()) 
			{
				foreach ($carts->getQuote()->getAllItems() as $item) {
					$_product = $item->getProduct();
					if($_product->getTypeId() != 'booking')
					{
						break;
					}
					$bookingModel = $this->_bookingFactory->create();
					$booking = $bookingModel->getBooking($_product->getId());
					if($booking && $booking->getId())
					{
						$qty = $item->getQty();
						$_customOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
						$customOptionsRequest = $_customOptions['info_buyRequest'];
						if(!isset($customOptionsRequest['check_in']) || (isset($customOptionsRequest['check_in']) && $customOptionsRequest['check_in'] == ''))
						{
							continue;
						}
						$checkIn = $this->_bkHelperDate->convertFormatDate($customOptionsRequest['check_in']);
						//vaildate item's time
						if(isset($customOptionsRequest['check_out']))
						{
							$checkOut = $this->_bkHelperDate->convertFormatDate($customOptionsRequest['check_out']);
						}
						else
						{
							$checkOut = $checkIn;
						}
						$arPrice = array();
						if($booking->getBookingType() == 'per_day')
						{
							$paramAddons = isset($customOptionsRequest['addons']) ? $customOptionsRequest['addons'] : array();
							$arAddonPrice = array();
							if($booking->getBookingTime() == 1 || $booking->getBookingTime() == 4)
							{
								$arPrice = $this->_bkSimplePriceHelper->getPriceBetweenDays($booking,$checkIn,$checkOut,$qty,$item->getId(),$paramAddons);
							}
							elseif($booking->getBookingTime() == 2)
							{
                                $serviceStart = isset($customOptionsRequest['service_start']) ? $customOptionsRequest['service_start'] : '';
                                $serviceEnd = isset($customOptionsRequest['service_end']) ? $customOptionsRequest['service_end'] : '';
                                $arPrice = $this->_bkSimplePriceHelper->getPricePerTime($booking,$checkIn,$checkOut,$serviceStart,$serviceEnd,$qty,$item->getId(),$paramAddons);
                            }
                            elseif($booking->getBookingTime() == 3)
                            {
                                $intervalsHours = isset($customOptionsRequest['intervals_hours']) ? $customOptionsRequest['intervals_hours'] : array();
                                $arPrice = $this->_intervalsPrice->getIntervalsHoursPrice($booking,$checkIn,$qty,$intervalsHours,$item->getId(),$paramAddons);
                            }
                            elseif ($booking->getBookingTime() == 5)
                            {
                                $persons = isset($customOptionsRequest['number_persons']) ? $customOptionsRequest['number_persons'] : array();
                                $arPrice = $this->_bkSimplePriceHelper->getBkTourPrice($booking,$checkIn,$checkOut,$qty,$item->getId(),$paramAddons,$persons);
                            }
						}
						elseif($booking->getBookingType() == 'hotel')
						{
							$roomId = $customOptionsRequest['room_id'];
							$roomModel = $this->_roomsFactory->create();
							$room = $roomModel->load($roomId);
							if($room)
							{
								$checkIn = $this->_bkHelperDate->convertFormatDate($customOptionsRequest['room_check_in']);
								$checkOut = $this->_bkHelperDate->convertFormatDate($customOptionsRequest['room_check_out']);
								$paramAddons = isset($customOptionsRequest['addons']) ? $customOptionsRequest['addons'] : array();
								$arPrice = $this->_roomPrice->getPriceBetweenDays($room,$checkIn,$checkOut,$qty,$item->getId(),$paramAddons);
								
							}
						}
						if(!count($arPrice) || $arPrice['str_error'] != '')
						{
							$bkError = true;
							break;
						}
					}
				}
				if($bkError)
				{
					throw new \Exception(__('Dates are not available. Please check again!'));
				}
			}
		}
    }
}