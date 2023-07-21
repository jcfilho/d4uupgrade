<?php
 
namespace Daytours\Bookingsystem\Controller\Booking;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;


class BookingIntervals extends \Magento\Framework\App\Action\Action
{
	protected $resultPageFactory;
	 /**
     * Result page factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory;
     */
    /**
     * @var \Daytours\Bookingsystem\Helper\Data
     */
    protected $_daytoursHelperData;

	protected $_resultJsonFactory;
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory,
        \Daytours\Bookingsystem\Helper\Data $daytoursHelperData
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
        $this->_daytoursHelperData = $daytoursHelperData;
    }
    public function execute()
    {

        $resultJson = $this->_resultJsonFactory->create();
        if( $this->_daytoursHelperData->ifProductIsTransfer($this->getRequest()->getParam('product')) ){
            $htmlResult = $this->_view->getLayout()->createBlock('Daytours\Bookingsystem\Block\Intervals')->setTemplate('Daytours_Bookingsystem::product/view/interval-result-transfer.phtml')->toHtml();
        }else{
            $htmlResult = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Intervals')->setTemplate('Daytours_Bookingsystem::product/view/interval-result.phtml')->toHtml();
        }

		$response = array('html_result'=>$htmlResult);
		return $resultJson->setData($response);
    }
}
 