<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Dashboard;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class View extends \Magebay\Bookingsystem\Controller\Adminhtml\Dashboard
{
	 /**
     * Result page factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory;
     */
	protected $_resultJsonFactory;
	public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory
    ) {
       parent::__construct($context,$coreRegistry,$resultPageFactory);
		$this->_resultJsonFactory = $resultJsonFactory;

    }
	public function execute()
	{
		
		$resultJson = $this->_resultJsonFactory->create();
		$htmlDetail = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Adminhtml\Dashboard')->setTemplate('Magebay_Bookingsystem::dashboard/report-detail.phtml')->toHtml();
		$response = array('html_detail'=> $htmlDetail );
		return $resultJson->setData($response);
	}

}