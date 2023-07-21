<?php

namespace Magebay\Bookingsystem\Controller\Adminhtml\Createorder;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class Intervals extends Action
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
		$htmlIntervals = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Adminhtml\Order\Create\Intervals')->setTemplate('Magebay_Bookingsystem::sales/order/bk_intervals_item.phtml')->toHtml();
		$response = array('html_intervals'=> $htmlIntervals);
		return $resultJson->setData($response);
    }
}
 