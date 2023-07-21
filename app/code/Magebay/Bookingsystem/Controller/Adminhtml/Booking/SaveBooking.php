<?php
 /** 
 ** Code for version 2.1 or more 
 */
namespace Magebay\Bookingsystem\Controller\Adminhtml\Booking;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magebay\Bookingsystem\Model\BookingsFactory;
use Magebay\Bookingsystem\Model\OptionsFactory;
use Magebay\Bookingsystem\Model\DiscountsFactory;
use Magebay\Bookingsystem\Model\FacilitiesFactory;
use Magebay\Bookingsystem\Model\IntervalhoursFactory;
use Magebay\Bookingsystem\Helper\Data as BookingHelper;

class SaveBooking extends Action
{
	 /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * Result page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
	 
    protected $_resultPageFactory;
	 /**
     * Result page factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory;
     */
	protected $_resultJsonFactory;
	 
	protected $_bookingFactory;
	protected $_optionsFactory;
	protected $_discountsFactory;
	protected $_intervalhoursFactory;
	protected $_bookingHelper;
	protected $_bkAct;
	function __construct
	(
		Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory,
		BookingsFactory $bookingFactory,
		OptionsFactory $optionsFactory,
		DiscountsFactory $discountsFactory,
		FacilitiesFactory $facilitiesFactory,
		IntervalhoursFactory $intervalhoursFactory,
		BookingHelper $bookingHelper,
		\Magebay\Bookingsystem\Model\ActFactory $bkAct
	)
	{
		parent::__construct($context);
		$this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_bookingFactory = $bookingFactory;
		$this->_optionsFactory = $optionsFactory;
		$this->_discountsFactory = $discountsFactory;
		$this->_facilitiesFactory = $facilitiesFactory;
		$this->_intervalhoursFactory = $intervalhoursFactory;
		$this->_bookingHelper = $bookingHelper;
		$this->_bkAct = $bkAct;
	}
	public function execute()
	{
		$params = $this->getRequest()->getParams();
		$bookingParams = isset($params['bookings']) ? $params['bookings'] : array();
		$productId = isset($bookingParams['booking_product_id']) ? $bookingParams['booking_product_id'] : 0;
		$storeId = isset($bookingParams['store_id']) ? $bookingParams['store_id'] : 0;
		$message = __('You can not save booking data');
		$tempBookingId = 0;
		$main_domain = $this->_bookingHelper->get_domain( $_SERVER['SERVER_NAME'] );
		$valid = true;
		if ( $main_domain != 'dev' ) {
            $rakes = $this->_bkAct->create()->getCollection();
            $rakes->addFieldToFilter('path', 'bookingsystem/act/key' );
            $valid = false;
            if ( count($rakes) > 0 ) {
                foreach ( $rakes as $rake )  {
                    if ( $rake->getExtensionCode() == md5($main_domain.trim($this->_bookingHelper->getStoreConfigData('bookingsystem/act/key')) ) ) {
                        $valid = true;	
                    }
                }
            }		
		}
		if(!$valid)
		{
			$message = __('Please Enter Key!');
		}
		elseif(count($bookingParams) && $productId > 0)
		{
			try{
				$bookingModel = $this->_bookingFactory->create();
                $isSaveAddress = $this->getRequest()->getParam('is_address',0);
                $enableAdress = $this->_bookingHelper->getFieldSetting('bookingsystem/setting/booking_address');
                $tempBookingId = 0;
				if($isSaveAddress == 0)
                {
                    $facilityModel = $discountModel = $this->_facilitiesFactory->create();
                    $facilityParams = isset($bookingParams['facilities']) ? $bookingParams['facilities'] : array();
                    $bookingModel->saveBooking($bookingParams,$productId);
                    $tempBookingId = $bookingModel->getId();
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
				elseif($enableAdress == 1 && $isSaveAddress == 1)
                {
                    $bookingModel->saveBookingAddressStore($bookingParams,$storeId);
                }
				$message = __('Booking Data have been saved success');
			}
			catch (\Exception $e) {
				$message = $e->getMessage();
			}
			
		}
		$resultJson = $this->_resultJsonFactory->create();
		$response = array('message'=> $message,'temp_booking_id'=>$tempBookingId);
		return $resultJson->setData($response);
	}
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebay_Bookingsystem::update_booking');
    }
}