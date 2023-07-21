<?php
 
namespace Magebay\Bookingsystem\Controller\Marketplace;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magebay\Bookingsystem\Model\BookingimagesFactory;
use Magebay\Bookingsystem\Model\Image as ImageModel;
use Magebay\Bookingsystem\Model\Upload as UploadImages;

class UploadImage extends Action
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
     * Image model
     *
     * @var  Magebay\Bookingsystem\Model\Image;
     */
	protected $_imageModel;
	/**
     * Image images
     *
     * @var  Magebay\Bookingsystem\Model\Upload;
     */
	protected $_uploadImages;
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
		ImageModel $ImageModel,
		UploadImages $uploadImages,
		BookingimagesFactory $bookingimages
	)
	{
		parent::__construct($context);
		$this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_imageModel = $ImageModel;
		$this->_uploadImages = $uploadImages;
		$this->_bookingimages = $bookingimages;
	}
	public function execute()
	{
		$status = false;
		$roomId = $this->_request->getParam('room_id',0);
		$resultJson = $this->_resultJsonFactory->create();
		//upload image
		$formData = $this->getRequest()->getParams();
		$formData['bkimage_path'] = $this->_uploadImages->uploadFileAndGetName('room_image', $this->_imageModel->getBaseDir(), $formData);
		$formData['bkimage_title'] = __('Room Image');
		$formData['bkimage_type'] = 'room';
		$formData['bkimage_data_id'] = $roomId;
		$modelImage = $this->_bookingimages->create();
		try{
			$modelImage->setData($formData)->save();
			$status = true;
			} catch (\Exception $e) {
				
		}
		$htmlImages = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Marketplace\RoomsPopup')->setData(array('bk_data_id'=>$roomId,'bk_data_type'=>'room'))->setTemplate('Magebay_Bookingsystem::marketplace/rooms/images.phtml')->toHtml();
		$response = array('html_images'=> $htmlImages,'status'=>$status);
		return $resultJson->setData($response);
	}
}