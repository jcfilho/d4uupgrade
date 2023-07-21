<?php
 
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Createorder;
 
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class SearchRooms extends Action
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
		$htmlListRooms = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Adminhtml\Order\Create\Rooms')->setTemplate('Magebay_Bookingsystem::sales/order/bk_list_rooms.phtml')->toHtml();
		$response = array('html_list_rooms'=> $htmlListRooms);
		return $resultJson->setData($response);
    }
}
 