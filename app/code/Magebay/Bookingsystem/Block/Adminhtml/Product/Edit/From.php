<?php
/**
 *
 * Code for version 2.1 or more
 */
namespace Magebay\Bookingsystem\Block\Adminhtml\Product\Edit;

use Magebay\Bookingsystem\Helper\Data as BkHelper;
use Magento\Directory\Model\Config\Source\Country;
use Magebay\Bookingsystem\Model\BookingsFactory;

class From extends \Magento\Backend\Block\Template
{
	protected $_product;
	/*  
	* @var Magebay\Bookingsystem\Helper\Data\BkText
	*/
	protected $_bkText;
	/*  
	* @var Magebay\Bookingsystem\Helper\Data\BkHelperDate
	*/
	protected $_bkHelperDate;
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
	protected $_template = 'Magebay_Bookingsystem::catalog/product/bk21/edit.phtml';
	protected $coreRegistry;
	protected $_productMetadata;
	protected $_bkAct;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
		\Magento\Framework\Registry $coreRegistry,
		\Magento\Catalog\Model\Product $product,
		\Magento\Framework\App\ProductMetadataInterface $productMetadata,
		BkHelper $bkHelper,
		Country $countryFactory,
		BookingsFactory $bookingsFactory,
		\Magebay\Bookingsystem\Model\ActFactory $bkAct,
        array $data = []
    ) {
        parent::__construct($context, $data);
		
		$this->coreRegistry = $coreRegistry;
		$this->_product = $product;
		$this->_productMetadata = $productMetadata;
		$this->_bkHelper = $bkHelper;
		$this->_countryFactory = $countryFactory;
		$this->_bookingFactory = $bookingsFactory;
		$this->_bkAct = $bkAct;
    }
	function getBookingProduct()
	{
		$bookingId = $this->getRequest()->getParam('booking_id',0);
		$storeId = $this->_request->getParam('store',0);
		$dataBooking = array(
			'booking_type'=>'',
			'booking_time'=>4,
			'booking_product_id'=>$bookingId,
			'booking_min_days'=>0,
			'booking_max_days'=>0,
			'booking_min_hours'=>0,
			'booking_max_hours'=>0,
			'booking_service_start'=>'',
			'booking_service_end'=>'',
			'booking_fee_night'=>10,
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
			'booking_state'=>'',
			'auto_address'=>'',
			'booking_lat'=>0,
			'booking_lon'=>0,
			'booking_id'=>0,
			'booking_type_intevals'=>0,
			'disable_days'=>'',
			'booking_tour_type'=>1,
		);
		if($bookingId > 0)
		{
			$bookingModel = $this->_bookingFactory->create();;
            $arrayAttributeSelect = array('*');
            $arAttributeConditions = array();$condition = '';
            $arrayBooking = array('*');
            $attributeSort = array();
            $bookingSort = '';
            $limit = 0;
            $curPage = 1;
			$booking = $bookingModel->getBooking($bookingId,$arrayAttributeSelect,$arAttributeConditions,$condition,$arrayBooking,$attributeSort,$bookingSort,$limit,$curPage,$storeId);
			if($booking && $booking->getId())
			{
				$dataBooking = $booking->getData();
			}
		}
		$dataBooking['store_id'] = $storeId;
		return $dataBooking;
	}
	function getBkCountriesOptions()
	{
		return $this->_countryFactory->toOptionArray();
	}
	/* get url ajax for booking product edit */
	function getArrayAjaxUrl($bookingId)
	{
		$bkhelper = $this->_bkHelper;
		$newOptionUrl = $bkhelper->getBkAdminAjaxUrl('bookingsystem/booking/newOption',array('booking_id'=>$bookingId));
		$newDiscountUrl = $bkhelper->getBkAdminAjaxUrl('bookingsystem/booking/newDiscount',array('booking_id'=>$bookingId));
		$urlFacilities = $bkhelper->getBkAdminAjaxUrl('bookingsystem/booking/facilities',array('booking_id'=>$bookingId));
		$urlSetupRentPrice = $bkhelper->getBkAdminAjaxUrl('bookingsystem/booking/setupRentPrice',array('booking_id'=>$bookingId));
		$urlSetupRoom = $bkhelper->getBkAdminAjaxUrl('bookingsystem/rooms/setupRoom');
		$urlStates = $bkhelper->getBkAdminAjaxUrl('bookingsystem/booking/getStates');
		$urlSaveBooking = $bkhelper->getBkAdminAjaxUrl('bookingsystem/booking/saveBooking',array('booking_id'=>$bookingId));
        $urlDeleteIntervals = $bkhelper->getBkAdminAjaxUrl('bookingsystem/booking/deleteOldIntervals',array('booking_id'=>$bookingId));
		return array(
			'url_new_option'=>$newOptionUrl,
			'url_new_discount'=>$newDiscountUrl,
			'url_facilities'=>$urlFacilities,
			'url_setup_rent_price'=>$urlSetupRentPrice,
			'url_setup_room'=>$urlSetupRoom,
			'url_states'=>$urlStates,
			'url_save_booking'=>$urlSaveBooking,
			'url_delete_intervals'=>$urlDeleteIntervals,
		);
	}
	function getFromAjaxUrl()
	{
		$bkhelper = $this->_bkHelper;
		$url = $bkhelper->getBkAdminAjaxUrl('bookingsystem/booking/getFoomBk');
		return $url;
	}
	function getChangeTypeAjaxUrl()
	{
		$bkhelper = $this->_bkHelper;
		$url = $bkhelper->getBkAdminAjaxUrl('bookingsystem/booking/changeBookingType');
		return $url;
	}
	function getBkRequest()
	{
		return $this->_request;
	}
	function getBkConfig($field)
	{
		return $this->_bkHelper->getFieldSetting($field,false);
	}
	function getCoreProduct($productId)
	{
		return $this->_product->load($productId);
	}
	function getBkAuthorization()
	{
		return $this->_authorization->isAllowed('Magebay_Bookingsystem::manage_bookings');
	}
	function checkBkKey()
	{
		$main_domain = $this->_bkHelper->get_domain( $_SERVER['SERVER_NAME'] );
		$valid = true;
		if ( $main_domain != 'dev' ) {
            $rakes = $this->_bkAct->create()->getCollection();
            $rakes->addFieldToFilter('path', 'bookingsystem/act/key' );
            $valid = false;
            if ( count($rakes) > 0 ) {
                foreach ( $rakes as $rake )  {
                    if ( $rake->getExtensionCode() == md5($main_domain.trim($this->_bkHelper->getStoreConfigData('bookingsystem/act/key')) ) ) {
                        $valid = true;	
                    }
                }
            }		
		}
		return $valid;
	}
	function  getBkHelper()
    {
        return $this->_bkHelper;
    }
}
