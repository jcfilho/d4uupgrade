<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Booking;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Backend\Model\Auth\Session as BackendSession;
use Magebay\Bookingsystem\Model\DiscountsFactory;

class NewDiscount extends Action
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
     * @var Magebay\Bookingsystem\Model\DiscountsFactory;
     */
	 protected $_discountsFactory;
	 
	 
	function __construct
	(
		Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory,
		BackendSession $backendSession,
		DiscountsFactory $discountsFactory
	)
	{
		parent::__construct($context);
		$this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_backendSession = $backendSession;
		$this->_discountsFactory = $discountsFactory;
	}
	public function execute()
	{
		$backendSession = $this->_backendSession;
		//what is kind of discount ? per_day or hotel
		$kindOfDicount = $this->_request->getParam('kind_of_discount','per_day');
		$maxId = $backendSession->getMaxDiscountId();
		if($maxId == 0)
		{
			$modelOptions = $this->_discountsFactory->create();
			$collection = $modelOptions->getCollection()
				->addFieldToSelect(array('discount_id'))
				->setOrder('discount_id','DESC');
			if(count($collection))
			{
				$maxId = $collection->getFirstItem()->getId();
			}
		}
		$maxId++;
		$backendSession->setMaxDiscountId($maxId);
		$resultJson = $this->_resultJsonFactory->create();
		$htmlDiscount = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Adminhtml\Booking')->setTemplate('Magebay_Bookingsystem::catalog/product/form-discount.phtml')->toHtml();
		if($kindOfDicount == 'hotel')
		{
			$htmlDiscount = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Adminhtml\Booking')->setTemplate('Magebay_Bookingsystem::catalog/product/rooms/form-discount.phtml')->toHtml();
		}
		$response = array('html_discount'=> $htmlDiscount);
		return $resultJson->setData($response);
	}
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebay_Bookingsystem::update_booking');
    }
}