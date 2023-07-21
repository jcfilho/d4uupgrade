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

class EditPriceSession extends Action
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
        $bookingId = $this->getRequest()->getParam('booking_id',0);
        $checkIn = $this->getRequest()->getParam('check_in','');
        $checkOut = $this->getRequest()->getParam('check_out','');
        $intervalsModel = $this->_intervalhoursFactory->create();
        $intervals = $intervalsModel->getIntervals($bookingId,$checkIn);
        $dataSend = array(
            'intervals_data'=>$intervals,
            'check_in'=>$checkIn,
            'check_out'=>$checkOut,
        );
        $resultJson = $this->_resultJsonFactory->create();
        $htmlCalendarForm = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Adminhtml\Calendars')->setData($dataSend)->setTemplate('Magebay_Bookingsystem::catalog/product/calendars/intervals-form.phtml')->toHtml();
        $response = array('html_intervals'=> $htmlCalendarForm);
        return $resultJson->setData($response);
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebay_Bookingsystem::update_booking');
    }
}