<?php
 
namespace Magebay\Bookingsystem\Controller\Booking;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;


class BookingRoom extends \Magento\Framework\App\Action\Action
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
		$htmlResult = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Rooms')->setTemplate('Magebay_Bookingsystem::product/view/room-result.phtml')->toHtml();
		$response = array('booking_result'=>$htmlResult);
		return $resultJson->setData($response);
    }
}
 