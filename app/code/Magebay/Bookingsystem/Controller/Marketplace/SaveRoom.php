<?php
 
namespace Magebay\Bookingsystem\Controller\Marketplace;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;
use Magebay\Bookingsystem\Model\RoomsFactory;
use Magebay\Marketplace\Model\ProductsFactory as MkProduct;

class SaveRoom extends Action
{
	 /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
	 
    protected $_resultPageFactory;
	 /**
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory;
     */
	protected $_resultJsonFactory;
	/**
     *
     * @var Magento\Customer\Model\Session
     */
    protected $_customerSession;
	/**
     * Result page factory
     *
     * @var \Magebay\Bookingsystem\Model\RoomsFactory;
     */
	 protected $_roomsFactory;
	function __construct
	(
		Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory,
		Session $customerSession,
		RoomsFactory $roomsFactory,
		MkProduct $mkProduct
	)
	{
		parent::__construct($context);
		$this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_customerSession = $customerSession;
		$this->_roomsFactory = $roomsFactory;
		$this->_mkProduct = $mkProduct;
	}
	public function execute()
	{
		$status = false;
		$messageStatus = '';
		$roomBookingId  = $this->_request->getParam('room_booking_id',0);
		$roomId = $this->_request->getParam('room_id',0);;
		$storeId = $this->_request->getParam('store_id',0);
		$resultJson = $this->_resultJsonFactory->create();
		$params = $this->_request->getParams();
		$customerSession = $this->_customerSession;
		if($customerSession->isLoggedIn())
		{
			$userId = $customerSession->getId();
			$mkProductModel = $this->_mkProduct->create();
			$collection = $mkProductModel->getCollection()
							->addFieldToFilter('product_id',$roomBookingId)
							->addFieldToFilter('user_id',$userId);
			if(count($collection))
			{
				$model = $this->_roomsFactory->create();
				try {
						$arDisableDays = isset($params['disable_days']) ? $params['disable_days'] : array();
						if(count($arDisableDays))
						{
							$params['disable_days'] = implode(',',$arDisableDays);
						}
						else
						{
							$params['disable_days'] = '';
						}
						$roomId = $model->saveBkRoom($params);
						$status = true;
						$messageStatus = __('You have saved Data');
					} catch (\Exception $e) {
						$messageStatus = $e->getMessage();
				}
			}
		}
		$htmlRoom = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Marketplace\RoomsPopup')->setData(array('bk_room_id'=>$roomId,'bk_store_id'=>$storeId,'room_booking_id'=>$roomBookingId))->setTemplate('Magebay_Bookingsystem::marketplace/popup_rooms.phtml')->toHtml();
		$htmlRooms = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Marketplace\RoomsPopup')->setData(array('booking_id'=>$roomBookingId,'bk_store_id'=>$storeId))->setTemplate('Magebay_Bookingsystem::marketplace/rooms.phtml')->toHtml();
		$response = array('html_room'=> $htmlRoom,'html_rooms'=>$htmlRooms,'status'=>$status);
		return $resultJson->setData($response);
	}
}