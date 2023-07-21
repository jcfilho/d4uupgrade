<?php
 
namespace Magebay\Bookingsystem\Controller\Booking;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;


class Booking extends \Magento\Framework\App\Action\Action
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
		$bookingId = $this->getRequest()->getParam('product',0);
		$htmlPersons = '';
		if($bookingId > 0)
        {
            $htmlPersons = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Booking')->setTemplate('Magebay_Bookingsystem::product/view/extract-persons.phtml')->toHtml();
        }
		$htmlResult = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Booking')->setTemplate('Magebay_Bookingsystem::product/view/simple-result.phtml')->toHtml();
		$response = array('html_result'=>$htmlResult,'html_person' => $htmlPersons);
		return $resultJson->setData($response);
    }
}
 