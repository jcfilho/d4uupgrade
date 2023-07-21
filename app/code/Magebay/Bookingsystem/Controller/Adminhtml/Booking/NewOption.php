<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Booking;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Backend\Model\Auth\Session as BackendSession;
use Magebay\Bookingsystem\Model\OptionsFactory;

class NewOption extends Action
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
     * @var Magento\Backend\Model\Auth\Session;
     */
	protected $_backendSession;
	/**
     * 
     * @var Magebay\Bookingsystem\Model\OptionsFactory;
     */
	 protected $_optionsFactory;
	function __construct
	(
		Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory,
		BackendSession $backendSession,
		OptionsFactory $optionsFactory
	)
	{
		parent::__construct($context);
		$this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_backendSession = $backendSession;
		$this->_optionsFactory = $optionsFactory;
	}
	public function execute()
	{
		$backendSession = $this->_backendSession;
		//what is options per_day or hotel
		$kindOfOption = $this->_request->getParam('kind_of_option','per_day');
		$maxId = $backendSession->getMaxOptionId();
		if($maxId == 0)
		{
			$modelOptions = $this->_optionsFactory->create();
			$collection = $modelOptions->getCollection()
				->addFieldToSelect(array('option_id'))
				->setOrder('option_id','DESC');
			if(count($collection))
			{
				$maxId = $collection->getFirstItem()->getId();
			}
		}
		$maxId++;
		$backendSession->setMaxOptionId($maxId);
		$resultJson = $this->_resultJsonFactory->create();
		$htmlOptions = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Adminhtml\Booking')->setTemplate('Magebay_Bookingsystem::catalog/product/form-options.phtml')->toHtml();
		if($kindOfOption == 'hotel')
		{
			$htmlOptions = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Adminhtml\Booking')->setTemplate('Magebay_Bookingsystem::catalog/product/rooms/form-options.phtml')->toHtml();
		}
		$response = array('html_option'=> $htmlOptions );
		return $resultJson->setData($response);
	}
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebay_Bookingsystem::update_booking');
    }
}