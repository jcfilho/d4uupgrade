<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Createorder;
 
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;


class BookingRoom extends Action
{
	protected $resultPageFactory;
	 /**
     * Result page factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory;
     */
	protected $_resultJsonFactory;
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
    }
    public function execute()
    {
		$resultJson = $this->_resultJsonFactory->create();
		$htmlResult = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Adminhtml\Order\Create\Rooms')->setTemplate('Magebay_Bookingsystem::sales/order/room-result.phtml')->toHtml();
		$response = array('booking_result'=>$htmlResult);
		return $resultJson->setData($response);
    }
}
 