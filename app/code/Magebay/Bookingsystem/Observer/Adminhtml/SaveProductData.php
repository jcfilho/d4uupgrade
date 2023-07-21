<?php
namespace Magebay\Bookingsystem\Observer\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Framework\App\RequestInterface;
use Magento\Backend\Model\Auth\Session as BackendSession;
use Magebay\Bookingsystem\Model\BookingsFactory;
use Magebay\Bookingsystem\Model\OptionsFactory;
use Magebay\Bookingsystem\Model\DiscountsFactory;
use Magebay\Bookingsystem\Model\FacilitiesFactory;
use Magebay\Bookingsystem\Model\IntervalhoursFactory;

class SaveProductData implements ObserverInterface
{
	protected $_backendSession;
	protected $_bookingFactory;
	protected $_optionsFactory;
	protected $_discountsFactory;
	protected $_intervalhoursFactory;
	/**
     * 
     * @var Magebay\Bookingsystem\Model\FacilitiesFactory;
     */
	 protected $_facilitiesFactory;
	protected $request;
	public function __construct(
				RequestInterface $request,
				BackendSession $backendSession,
				BookingsFactory $bookingFactory,
				OptionsFactory $optionsFactory,
				DiscountsFactory $discountsFactory,
				FacilitiesFactory $facilitiesFactory,
				IntervalhoursFactory $intervalhoursFactory
			)
		{
			
			$this->request = $request;
			$this->_backendSession = $backendSession;
			$this->_bookingFactory = $bookingFactory;
			$this->_optionsFactory = $optionsFactory;
			$this->_discountsFactory = $discountsFactory;
			$this->_facilitiesFactory = $facilitiesFactory;
			$this->_intervalhoursFactory = $intervalhoursFactory;
		}
    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $_product = $observer->getEvent()->getProduct();
		$params = $this->request->getPost();
		$bookingParams = isset($params['bookings']) ? $params['bookings'] : array();
		$okSecurity = false;
		if(isset($bookingParams['booking_security']))
		{
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$adminSession = $this->_backendSession;
			$strSecurity = $adminSession->getBookingSecurity();
			if($strSecurity == $bookingParams['booking_security'])
			{
				$okSecurity = true;
				$strCode = md5('booking'.time());
				$adminSession->setBookingSecurity($strCode);
			}
		}
		if(count($bookingParams) && $_product->getTypeId() == 'booking' && $okSecurity)
		{
			$bookingModel = $this->_bookingFactory->create();
			$productId = $_product->getId();
			/* if($bookingParams['magebay_bk_save_split_button_duplicate_button'] == 1)
			{
				echo $productId;
				exit();
			} */
			if($bookingParams['booking_temp_id'] == 0)
			{
					//first save
					$firstData = array(
							'booking_type'=>$bookingParams['booking_type'],
							'booking_product_id'=>$productId,
							'booking_temp_id'=>$bookingParams['booking_temp_id'],
							'booking_time'=>1
							);
					$bookingModel->saveBooking($firstData,$productId);
			}
			else
			{
				$bookingModel->saveBooking($bookingParams,$productId);
				//save facilities
				$facilityModel = $discountModel = $this->_facilitiesFactory->create();
				$facilityParams = isset($bookingParams['facilities']) ? $bookingParams['facilities'] : array();
				if($bookingParams['booking_type'] == 'per_day')
				{
					//save addon slel options
					$sellOptions = $this->_optionsFactory->create();
					$optionsParams = isset($bookingParams['options']) ? $bookingParams['options'] : array();
					$sellOptions->saveBkOptions($optionsParams,$productId);
					//booking discount
					$discountModel = $this->_discountsFactory->create();
					$discountParams = isset($bookingParams['discounts']) ? $bookingParams['discounts'] : array();
					$discountModel->saveBkDiscounts($discountParams,$productId);
					//intervals hours
					$hoursInter = $this->_intervalhoursFactory->create();
					$hoursInter->saveIntervals($bookingParams,$productId);
					$facilityModel->saveBkFacilities($facilityParams,$productId);
				}
				else
				{
					$facilityModel->saveBkFacilities($facilityParams,$productId,'hotel');
				}
				
			}
		}
        return $this;
    }
}
