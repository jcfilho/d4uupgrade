<?php
/**
 ** Code for version 2.1 or more
 */

namespace Magebay\Bookingsystem\Controller\Marketplace;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magebay\Bookingsystem\Model\Bookings;
use Magebay\Bookingsystem\Model\IntervalhoursFactory;

class SaveInterval extends \Magento\Framework\App\Action\Action
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
    protected  $_bookings;
    protected $_intervalhoursFactory;
    function __construct
    (
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        Bookings $bookings,
        IntervalhoursFactory $interalhoursFactory
    )
    {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_bookings = $bookings;
        $this->_intervalhoursFactory = $interalhoursFactory;
    }
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if($params['intervalhours_id'] == 0)
        {
            unset($params['intervalhours_id']);
        }
        //validate time;
        $intervalsModel = $this->_intervalhoursFactory->create();
        $bookingId = isset($params['intervalhours_booking_id']) ? (int)$params['intervalhours_booking_id'] : 0;
        $okayVaidate = false;
        //$currentIntervals = $intervalsModel->getIntervals($bookingId);
        $intervalTime = '';
        if($bookingId > 0)
        {
            $booking = $this->_bookings->getBooking($bookingId);
            if($booking && $booking->getId() > 0)
            {
                $params['start_time'] = '';
                if($params['start_time_hour'] != '' && $params['start_time_minute'] != '' )
                {
                    $params['start_time_hour']  = (isset($params['start_time_type']) && $params['start_time_type'] == 2 && $params['start_time_hour'] != 12) ?  $params['start_time_hour'] + 12 : $params['start_time_hour'];
                    $params['start_time_hour'] = $params['start_time_hour'] < 10 ? '0'.$params['start_time_hour'] : $params['start_time_hour'];
                    $params['start_time'] = $params['start_time_hour'].':'.$params['start_time_minute'];
                }
                $params['end_time'] = '';
                if($params['end_time_hour'] != '' && $params['end_time_minute'] != '' )
                {
                    $params['end_time_hour']  = (isset($params['end_time_type']) && $params['end_time_type'] == 2 && $params['end_time_hour'] != 12) ?  $params['end_time_hour'] + 12 : $params['end_time_hour'];
                    $params['end_time'] = $params['end_time_hour'].':'.$params['end_time_minute'];
                }
                if($params['start_time'] != '' && $params['end_time'] != '')
                {
                    //echo $params['start_time'];
                    $txtServiceStart = strtotime($params['start_time'].":00");
                    $txtServiceEnd = strtotime($params['end_time'].":00");
                    //echo $txtServiceStart . $txtServiceEnd;
                    if($params['end_time'] == '0:00' || $txtServiceStart < $txtServiceEnd)
                    {
                        $okayVaidate = true;
                        $intervalTime = $params['start_time'].'_'.$params['end_time'];
                        $intervalTime = str_replace(':','_',$intervalTime);
                    }
                }

            }
        }

        $status = 'bk_error';
        $htmlCalendarForm = '';
        if(($params['intervalhours_label'] != '' && strlen($params['intervalhours_label']) > 100))
        {
            $okayVaidate = false;
        }
        if($okayVaidate && $intervalTime != '')
        {
            try{
                $checkIn = isset($params['intervalhours_check_in']) ? trim($params['intervalhours_check_in']) : '';
                $checkOut = isset($params['intervalhours_check_out']) ? trim($params['intervalhours_check_out']) : '';
                $dataSave = $params;
                $dataSave['intervalhours_booking_time'] = $intervalTime;
                $intervalsModel->setData($dataSave)->save();
                $intervals = $intervalsModel->getIntervals($bookingId);
                $dataSend = array(
                    'intervals_data'=>$intervals,
                    'check_in'=>$checkIn,
                    'check_out'=>$checkOut,
                );
                $htmlCalendarForm = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Marketplace\Calendars')->setData($dataSend)->setTemplate('Magebay_Bookingsystem::marketplace/calendars/interval-items.phtml')->toHtml();
                $status = 'success';
            }
            catch (\Exception $e)
            {
                $status = $e->getMessage();
            }
        }


        $resultJson = $this->_resultJsonFactory->create();
        $response = array('status'=>$status,'html_intervals'=> $htmlCalendarForm);
        return $resultJson->setData($response);
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebay_Bookingsystem::update_booking');
    }
}