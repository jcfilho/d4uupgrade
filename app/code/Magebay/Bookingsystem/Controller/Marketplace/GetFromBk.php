<?php
 
namespace Magebay\Bookingsystem\Controller\Marketplace;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;

class GetFromBk extends Action
{
	 /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * Result page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
	 
    protected $_resultPageFactory;
	 /**
     * Result page factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory;
     */
	protected $_resultJsonFactory;
	/**
     *
     * @var Magento\Customer\Model\Session
     */
    protected $_customerSession;
	function __construct
	(
		Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory,
		Session $customerSession
	)
	{
		parent::__construct($context);
		$this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_customerSession = $customerSession;
	}
	public function execute()
	{
		$htmlFrom = '';
		if($this->_customerSession->isLoggedIn())
		{
			$htmlFrom = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Marketplace\MkBooking')->setTemplate('Magebay_Bookingsystem::marketplace/bk_form_content.phtml')->toHtml();
		}
		else
		{
			$htmlFrom = __('You have to login!');
		}
		$resultJson = $this->_resultJsonFactory->create();
		$response = array('html_from'=> $htmlFrom);
		return $resultJson->setData($response);
	}
}