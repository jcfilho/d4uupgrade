<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Createorder;
 
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class ViewRoom extends Action
{
	 /**
     * Result page factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory;
     */
	protected $_resultJsonFactory;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
    */
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
		$htmlRoomDetail = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Adminhtml\Order\Create\Rooms')->setTemplate('Magebay_Bookingsystem::sales/order/bk_room_detail.phtml')->toHtml();
		$response = array('html_room_detail'=> $htmlRoomDetail);
		return $resultJson->setData($response);
    }
}
 