<?php
 
namespace Daytours\Bookingsystem\Controller\Booking;
 
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

    protected $_daytoursHelperData;

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

        if( $this->_daytoursHelperData->ifProductIsTransfer($this->getRequest()->getParam('booking_id')) ){
            //if is transfer product
            $htmlIntervals = $this->_view->getLayout()
                ->createBlock('Magebay\Bookingsystem\Block\Intervals')
                ->setData(['calendar_number' => $this->getRequest()->getParam('calendar_number')])
                ->setTemplate('Daytours_Bookingsystem::product/view/bk_intervals_item_transfer.phtml')->toHtml();
        }else{
            $htmlIntervals = $this->_view->getLayout()
                ->createBlock('Magebay\Bookingsystem\Block\Intervals')
                ->setTemplate('Daytours_Bookingsystem::product/view/bk_intervals_item.phtml')->toHtml();
        }


		$response = array('html_intervals'=> $htmlIntervals);
		return $resultJson->setData($response);
    }
}
 