<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Roomtypes;
 
use Magebay\Bookingsystem\Controller\Adminhtml\Roomtypes;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magebay\Bookingsystem\Model\RoomtypesFactory;
use Magebay\Bookingsystem\Model\Image as ImageModel;
use Magebay\Bookingsystem\Model\Upload as UploadImages;
use Magebay\Bookingsystem\Helper\BkText as BkHelperText;
use Magebay\Bookingsystem\Model\RoomsFactory;

class MassDelete extends Roomtypes
{
	protected $_roomsFactory; 
	
	public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        RoomtypesFactory $foomtypesFactory,
		ImageModel $ImageModel,
		UploadImages $uploadImages,
		BkHelperText $bkHelperText,
		RoomsFactory $roomsFactory
    ) 
	{
		
		parent::__construct($context,$coreRegistry,$resultPageFactory,$foomtypesFactory,$ImageModel,$uploadImages,$bkHelperText);
		$this->_roomsFactory = $roomsFactory;
    }
   /**
    * @return void
    */
	public function execute()
   {
      // Get IDs of the selected Roomtypes
		$roomtypeIds = $this->getRequest()->getParam('roomtypes');
		//check before delete
		$ok = true;
		foreach($roomtypeIds as $roomtypeId2)
		{
			$roomsModel = $this->_roomsFactory->create();
			$collection = $roomsModel->getCollection()
					->addFieldToFilter('room_type',$roomtypeId2);
			$room = $collection->getFirstItem();
			if($room)
			{
				if($room->getId())
				{
					$ok  = false;
					break;
				}
			}
		}
		if($ok)
		{
			 foreach ($roomtypeIds as $roomtypeId) {
				try {
					
				   /** @var $RoomtypesModel \Magebay\Bookingsystem\Model\Roomtypes */
					$roomtypesModel = $this->_roomtypesFactory->create();
					$roomtypesModel->load($roomtypeId)->delete();
				} catch (\Exception $e) {
					$this->messageManager->addError($e->getMessage());
				}
			}
			if (count($roomtypeIds)) {
				$this->messageManager->addSuccess(
					__('A total of %1 record(s) were deleted.', count($roomtypeIds))
				);
			}
		}
		else
		{
			$this->messageManager->addError(
					__('You can not delete Room type, Please check again')
				);
		}
        $this->_redirect('*/*/index');
	}
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Magebay_Bookingsystem::add_facility');
	}
}