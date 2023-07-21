<?php
 
namespace Magebay\Bookingsystem\Controller\Marketplace;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;
use Magebay\Bookingsystem\Model\CalendarsFactory;
use Magebay\Bookingsystem\Model\RoomsFactory;
use Magebay\Marketplace\Model\ProductsFactory as MkProduct;
use Magebay\Bookingsystem\Model\Intervalhours;

class CalendarSave extends Action
{
	 /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
	/**
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
	 
    protected $_resultPageFactory;
	 /**
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory;
     */
	 
	protected $_resultJsonFactory;
	/**
     *
     * @var \Magebay\Bookingsystem\Model\CalendarsFactory;
     */
	 protected $_calendarsFactory;
	 /**
     *
     * @var \Magebay\Bookingsystem\Model\RoomsFactory;
     */
	 protected $_roomsFactory;
	 /**
     *
     * @var \Magebay\Marketplace\Model\ProductsFactory
     */
	 protected $_mkProduct;
      /**
     * @var \Magebay\Bookingsystem\Model\Intervalhours;
     */
     protected $_intervalhours;
	function __construct
	(
		Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory,
		Session $customerSession,
		MkProduct $mkProduct,
		CalendarsFactory $calendarsFactory,
		RoomsFactory $roomsFactory,
        Intervalhours $intervalhours
	)
	{
		parent::__construct($context);
		$this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_customerSession = $customerSession;
		$this->_calendarsFactory = $calendarsFactory;
		$this->_roomsFactory = $roomsFactory;
		$this->_mkProduct = $mkProduct;
        $this->_intervalhours = $intervalhours;
	}
	public function execute()
	{
		$status = false;
        $note = 0;
		$messageStatus = '';
		$bookingId = $this->_request->getParam('booking_id',0);
		$bookingType = $this->_request->getParam('booking_type','per_day');
        $bookingTime = $this->_request->getParam('booking_time',1);
        $calendarId = (int)$this->getRequest()->getParam('calendar_id',0);
		$resultJson = $this->_resultJsonFactory->create();
		$customerSession = $this->_customerSession;
		$dataSend = array(
					'booking_id'=>$bookingId,
					'booking_type'=>$bookingType
				);
		if($customerSession->isLoggedIn())
		{
			$userId = $customerSession->getId();
			//get product 
			$mkProductModel = $this->_mkProduct->create();
			$productId = $bookingId;
			if($bookingType == 'hotel')
			{
				$productId = 0;
				$roomsModel = $this->_roomsFactory->create();
				$room = $roomsModel->load($bookingId);
				if($room && $room->getId())
				{
					$productId = $room->getRoomBookingId();
				}
			}
			$collection = $mkProductModel->getCollection()
							->addFieldToFilter('product_id',$productId)
							->addFieldToFilter('user_id',$userId);
			if(count($collection))
			{
				$params = $this->_request->getParams();
				$model = $this->_calendarsFactory->create();
				try {
                        if($bookingTime == 3)
                        {
                            $newCheckIn = $this->getRequest()->getParam('item_day_start_date','');
                            $newCheckOut = $this->getRequest()->getParam('item_day_end_date','');
                            $paramsTimSlot = $this->getRequest()->getParam('time_slot',array());
                            if($calendarId > 0 && count($paramsTimSlot))
                            {
                                $timeSlots = $this->_intervalhours->saveTimeSlots($bookingId,$paramsTimSlot,$calendarId,$newCheckIn,$newCheckOut,false);
                                $totalQty = $timeSlots['total_qty'];
                                $minPirce = $timeSlots['min_price'];
                                if($totalQty > 0)
                                {
                                    $model->setData(array('calendar_qty'=>$totalQty,'calendar_price'=>$minPirce))->setId($calendarId)->save();
                                    $params['item_day_qty'] = $totalQty;
                                    $params['item_day_price'] = $minPirce;
                                    $status = true;
                                    $messageStatus = __('You have saved Data');
                                }
                                else
                                {
                                    $status = false;
                                    $note = 1;
                                    $messageStatus = __('Please check Time Slot data');

                                }
                            }
                            else
                            {
                                $newCalendarId = 0;
                                if($this->_customerSession->getIsUpdateCaledar() && $this->_customerSession->getIsUpdateCaledar() == 1)
                                {
                                    $newCalendarId = $model->saveBkCalendars($params,false);
                                    $this->_customerSession->setNewCalendarId($newCalendarId);
                                }
                                if($newCalendarId == 0)
                                {
                                    $newCalendarId = $this->_customerSession->getNewCalendarId();
                                }
                                if($newCalendarId > 0 && count($paramsTimSlot))
                                {
                                    $timeSlots = $this->_intervalhours->saveTimeSlots($bookingId,$paramsTimSlot,$newCalendarId,$newCheckIn,$newCheckOut,false);
                                    $totalQty = $timeSlots['total_qty'];
                                    $minPirce = $timeSlots['min_price'];
                                    if($totalQty > 0)
                                    {
                                        $model->setData(array('calendar_qty'=>$totalQty,'calendar_price'=>$minPirce))->setId($newCalendarId)->save();
                                        $this->_customerSession->setIsUpdateCaledar(1);
                                        $status = true;
                                        $messageStatus = __('You have saved Data');
                                    }
                                    else
                                    {
                                        $status = false;
                                        $note = 1;
                                        $messageStatus = __('Please check Time Slot data');
                                        $this->_customerSession->setIsUpdateCaledar(0);
                                    }
                                }
                            }
                        }
                        else
                        {
                            $model->saveBkCalendars($params,false);
                            $status = true;
                            $messageStatus = __('You have saved Data');
                        }

					} catch (\Exception $e) {
						$messageStatus = $e->getMessage();
				}
			}
		}
		$htmlCalendarItems = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Marketplace\Calendars')->setData($dataSend)->setTemplate('Magebay_Bookingsystem::marketplace/calendars/items.phtml')->toHtml();
		$response = array('html_calendar_items'=> $htmlCalendarItems,'status'=>$status,'message'=>$messageStatus,'note'=>$note);
		return $resultJson->setData($response);
	}
}