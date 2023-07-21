<?php
namespace Magebay\Bookingsystem\Block\Marketplace;

use Magento\Framework\View\Element\Template;
use Magebay\Bookingsystem\Helper\Data as BkHelper;
use Magento\Directory\Model\Config\Source\Country;
use Magebay\Bookingsystem\Model\Bookings;

class MkBooking extends Template
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
    protected $coreRegistry;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\Product $product,
        BkHelper $bkHelper,
        Country $countryFactory,
        Bookings $bookingsFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->coreRegistry = $coreRegistry;
        $this->_product = $product;
        $this->_bkHelper = $bkHelper;
        $this->_countryFactory = $countryFactory;
        $this->_bookingFactory = $bookingsFactory;
    }
    function getBookingProduct()
    {

        $bookingId = $this->getRequest()->getParam('booking_id',0);
        $storeId = $this->getRequest()->getParam('store',0);;
        $dataBooking = array(
            'booking_type'=>'',
            'booking_time'=>1,
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
            'disable_days'=>'',
            'booking_type_intevals'=>1,
            'booking_tour_type'=>1
        );
        if($bookingId > 0)
        {
            $bookingModel = $this->_bookingFactory;
            $arrayAttributeSelect = array('*');
            $arAttributeConditions = array();
            $condition = '';
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
    function getFromAjaxUrl()
    {
        $bkhelper = $this->_bkHelper;
        $url = $bkhelper->formatUrlPro($this->getUrl('bookingsystem/marketplace/GetFromBk'));
        return $url;
    }
    function getCoreProduct($productId)
    {
        return $this->_product->load($productId);
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
        $urlSaveBooking = $bkhelper->formatUrlPro($this->getUrl('bookingsystem/marketplace/saveBooking/booking_id/'.$bookingId));
        $urlDeleteIntervals = $bkhelper->formatUrlPro($this->getUrl('bookingsystem/marketplace/deleteOldIntervals/booking_id/'.$bookingId));
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