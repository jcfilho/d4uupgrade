<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Rooms;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magebay\Bookingsystem\Model\BookingimagesFactory;

class DeleteImage extends Action
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
     * Image images
     *
     * @var  Magebay\Bookingsystem\Model\Bookingimages;
     */
	protected $_bookingimages;
	function __construct
	(
		Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory,
		BookingimagesFactory $bookingimages
	)
	{
		parent::__construct($context);
		$this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_bookingimages = $bookingimages;
	}
	public function execute()
	{
		$status = false;
		$roomId = $this->_request->getParam('image_room_id',0);
		$imageId = $this->_request->getParam('image_id',0);
		$resultJson = $this->_resultJsonFactory->create();
		//upload image
		if($imageId > 0)
		{
			$modelImage = $this->_bookingimages->create();
			try{
				$modelImage->setId($imageId)->delete();
				$status = true;
				} catch (\Exception $e) {
					
			}
		}
		$htmlImages = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Adminhtml\RoomsPopup')->setData(array('bk_data_id'=>$roomId,'bk_data_type'=>'room'))->setTemplate('Magebay_Bookingsystem::catalog/product/rooms/images.phtml')->toHtml();
		$response = array('html_images'=> $htmlImages,'status'=>$status);
		return $resultJson->setData($response);
	}
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebay_Bookingsystem::update_booking');
    }
}