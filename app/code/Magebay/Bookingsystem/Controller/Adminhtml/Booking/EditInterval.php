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
use Magebay\Bookingsystem\Model\IntervalhoursFactory;

class EditInterval extends Action
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
    protected $_intervalhoursFactory;
    function __construct
    (
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        IntervalhoursFactory $interalhoursFactory
    )
    {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_intervalhoursFactory = $interalhoursFactory;
    }
    public function execute()
    {
        $checkIn = $this->getRequest()->getParam('check_in','');
        $checkOut = $this->getRequest()->getParam('check_out','');
        $bookingId = $this->getRequest()->getParam('booking_id',0);
        $interval = array(
            'intervalhours_id'=>0,
            'intervalhours_booking_id'=>$bookingId,
            'intervalhours_quantity'=>0,
            'intervalhours_booking_time'=>'',
            'intervalhours_check_in'=>$checkIn,
            'intervalhours_check_out'=>$checkOut,
            'intervalhours_status'=>1,
            'intervalhours_price'=>0,
            'intervalhours_label'=>'',

        );
        $status = 'error';
        $intervalId = $this->getRequest()->getParam('interval_id',0);
        $intervalsModel = $this->_intervalhoursFactory->create();
        if($intervalId > 0)
        {
            $tempInterval = $intervalsModel->load($intervalId);
            if($tempInterval && $tempInterval->getId())
            {
                $interval = $tempInterval->getData();
                //$status = 'success';
            }
        }
        $status = 'success';
        $dataSend = array(
            'interval_data'=>$interval
        );
        $resultJson = $this->_resultJsonFactory->create();
        $htmlCalendarForm = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Adminhtml\Calendars')->setData($dataSend)->setTemplate('Magebay_Bookingsystem::catalog/product/calendars/interval-form.phtml')->toHtml();
        $response = array('html_interval'=> $htmlCalendarForm,'status'=>$status);
        return $resultJson->setData($response);
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebay_Bookingsystem::update_booking');
    }
}