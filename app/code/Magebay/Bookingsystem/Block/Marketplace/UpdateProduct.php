<?php 
namespace Magebay\Bookingsystem\Block\Marketplace;

use Magento\Framework\View\Element\Template;
use Magento\Backend\Model\Auth\Session as BackendSession;
use Magebay\Bookingsystem\Helper\Data as BkHelper;
use Magento\Directory\Model\Config\Source\Country;
use Magebay\Bookingsystem\Model\BookingsFactory;

class UpdateProduct extends Template
{
	/*  
	* @var Magebay\Bookingsystem\Helper\Data\BkText
	*/
	protected $_bkText;
	/*  
	* @var Magebay\Bookingsystem\Helper\Data\BkHelperDate
	*/
	protected $_bkHelperDate;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
	/**
     * @param \Magento\Backend\Model\Auth\Session
     * 
     */
	 
	protected $_backendSession;
	/**
     * @param \Magebay\Bookingsystem\Helper\Data
     * 
     */
	protected $_bkHelper;
	/**
     * @param \Magento\Directory\Model\Config\Source\CountryFactory
     * 
     */
	protected $_bookingFactory;
	protected $_countryFactory;
	protected $coreRegistry;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
		\Magento\Framework\Registry $coreRegistry,
		BackendSession $backendSession,
		BkHelper $bkHelper,
		Country $countryFactory,
		BookingsFactory $bookingsFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
		
		$this->coreRegistry = $coreRegistry;
		$this->_backendSession = $backendSession;
		$this->_bkHelper = $bkHelper;
		$this->_countryFactory = $countryFactory;
		$this->_bookingFactory = $bookingsFactory;
		$this->resetBkAdminSession();
    }
	public function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }
	function getBookingProduct()
	{
		$storeId = $this->_bkHelper->getbkCurrentStore();
		$_product = $this->getBKProduct();
		$dataBooking = array(
			'booking_type'=>'',
			'booking_time'=>1,
			'booking_product_id'=>0,
			'booking_min_days'=>0,
			'booking_max_days'=>0,
			'booking_min_hours'=>0,
			'booking_max_hours'=>0,
			'booking_service_start'=>'',
			'booking_service_end'=>'',
			'booking_fee_night'=>0,
			'booking_time_slot'=>0,
			'booking_time_buffer'=>0,
			'booking_show_finish'=>1,
			'booking_show_qty'=>1,
			'show_qty_avaliable'=>1,
			'booking_phone'=>'',
			'booking_email'=>'',
			'booking_zipcode'=>'',
			'booking_city'=>'',
			'booking_address'=>'',
			'booking_country'=>'',
			'booking_state_id'=>0,
			'booking_id'=>0,
			'disable_days'=>'',
		);
		if($_product->getId())
		{
			$bookingModel = $this->_bookingFactory->create();;
			$booking = $bookingModel->getBooking($_product->getId());
			if($booking && $booking->getId())
			{
				$dataBooking = $booking->getData();
			}
		}
		$dataBooking['store_id'] = $storeId;
		return $dataBooking;
	}
	function resetBkAdminSession()
	{

		$adminSession = $this->_backendSession;
		$strCode = md5('booking'.time());
		$adminSession->setBookingSecurity($strCode);
		$adminSession->setMaxOptionId(0);
		$adminSession->setMaxDiscountId(0);
	}
	function getBkAdminSession()
	{
		$adminSession = $this->_backendSession; 
		$arAdminSession['booking_security'] = $adminSession->getBookingSecurity();
		return $arAdminSession;
	}
	function getBkCountriesOptions()
	{
		return $this->_countryFactory->toOptionArray();
	}
	/* get url ajax for booking product edit */
	function getArrayAjaxUrl($bookingId)
	{
		$bkhelper = $this->_bkHelper;
		$newOptionUrl = $bkhelper->formatUrlPro($this->getUrl('bookingsystem/marketplace/newOption/booking_id/'.$bookingId));
		$newDiscountUrl = $bkhelper->formatUrlPro($this->getUrl('bookingsystem/marketplace/newDiscount/booking_id/'.$bookingId));
		$urlFacilities = $bkhelper->formatUrlPro($this->getUrl('bookingsystem/marketplace/facilities/booking_id/'.$bookingId));
		$urlSetupRentPrice = $bkhelper->formatUrlPro($this->getUrl('bookingsystem/marketplace/setupRentPrice/booking_id/'.$bookingId));
		$urlSetupRoom = $bkhelper->formatUrlPro($this->getUrl('bookingsystem/marketplace/setupRoom'));
		$urlStates = $bkhelper->formatUrlPro($this->getUrl('bookingsystem/marketplace/getStates'));
		return array(
			'url_new_option'=>$newOptionUrl,
			'url_new_discount'=>$newDiscountUrl,
			'url_facilities'=>$urlFacilities,
			'url_setup_rent_price'=>$urlSetupRentPrice,
			'url_setup_room'=>$urlSetupRoom,
			'url_states'=>$urlStates,
		);
	}
	function getBkHelper()
	{
		return $this->_bkHelper;
	}
	function getBkRequest()
	{
		return $this->_request;
	}
	function getBkConfig($field)
	{
		return $this->_bkHelper->getFieldSetting($field,false);
	}
}

?>