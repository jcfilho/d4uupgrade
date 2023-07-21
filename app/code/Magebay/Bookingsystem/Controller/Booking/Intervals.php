<?php
 
namespace Magebay\Bookingsystem\Controller\Booking;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class Intervals extends \Magento\Framework\App\Action\Action
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
		$htmlIntervals = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Intervals')->setTemplate('Magebay_Bookingsystem::product/view/bk_intervals_item.phtml')->toHtml();
		$response = array('html_intervals'=> $htmlIntervals);
		return $resultJson->setData($response);
    }
}
 