<?php
 
namespace Daytours\Bookingsystem\Controller\Booking;
 
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
    /**
     * @var \Daytours\Bookingsystem\Helper\Data
     */
    protected $_daytoursHelperData;

    /**
     * Booking constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param \Daytours\Bookingsystem\Helper\Data $daytoursHelperData
     */
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
		$bookingId = $this->getRequest()->getParam('product',0);
		$htmlPersons = '';
		if($bookingId > 0)
        {
            $htmlPersons = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Booking')->setTemplate('Magebay_Bookingsystem::product/view/extract-persons.phtml')->toHtml();
        }


        if( $this->_daytoursHelperData->ifProductIsTransfer($this->getRequest()->getParam('product')) ){
            /*If is transfer*/
            $htmlResult = $this->_view->getLayout()->createBlock('Daytours\Bookingsystem\Block\Booking')->setTemplate('Daytours_Bookingsystem::product/view/simple-result-transfer.phtml')->toHtml();
        }else{
            $htmlResult = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Booking')->setTemplate('Daytours_Bookingsystem::product/view/simple-result.phtml')->toHtml();
        }


		$response = array('html_result'=>$htmlResult,'html_person' => $htmlPersons);
		return $resultJson->setData($response);
    }
}
 