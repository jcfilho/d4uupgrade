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

class DeleteOldIntervals extends Action
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
        IntervalhoursFactory $intervalhoursFactory
    )
    {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_intervalhoursFactory = $intervalhoursFactory;
    }
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $bookingParams = isset($params['bookings']) ? $params['bookings'] : array();
        $productId = isset($bookingParams['booking_product_id']) ? $bookingParams['booking_product_id'] : 0;
        $status = 'error';
        $message = __('Delete Data faild. Please try again.');
        try{
            $hoursInter = $this->_intervalhoursFactory->create();
            $hoursInter->deleteIntervalsHours($productId);
            $message = __('You have update data successfully');
            $status = 'success';

        }catch (\Exception $e)
        {

        }
        $resultJson = $this->_resultJsonFactory->create();
        $response = array('message'=> $message,'status'=>$status);
        return $resultJson->setData($response);
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebay_Bookingsystem::update_booking');
    }
}