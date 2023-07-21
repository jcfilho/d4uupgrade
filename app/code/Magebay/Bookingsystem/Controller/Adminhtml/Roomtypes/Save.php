<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Roomtypes;
 
use Magebay\Bookingsystem\Controller\Adminhtml\Roomtypes;

class Save extends Roomtypes
{
   /**
     * @return void
     */
	public function execute()
	{
		$isPost = $this->getRequest()->getPost();
		$roomtypeId = 0;
		$isNew = true;
		$arCurrentTitle = array();
		$defaultTitle = '';
		$titleUseDefault = 1;
		$roomtypeTitle  = '';
		if ($isPost) 
		{
			$formData = $this->getRequest()->getParam('roomtypes');
			$bkHelperText = $this->_bkHelperText;
			$roomtypeTitle = isset($formData['roomtype_title']) ? $formData['roomtype_title'] : '';
			$storeId = isset($formData['store_id']) ? $formData['store_id'] : 0;
			$roomtypesModel = $this->_roomtypesFactory->create();
			// $roomtypeId = $this->getRequest()->getParam('roomtype_id');
			$roomtypeId = isset($formData['roomtype_id']) ? $formData['roomtype_id'] : 0;
			if($roomtypeId) {
				$roomtypesModel->load($roomtypeId);
				if($roomtypesModel->getId())
				{
					$isNew = false;
					$defaultTitle = $roomtypesModel->getRoomtypeTitle();
					if($storeId > 0)
					{
						unset($formData['roomtype_title']);
						if(isset($formData['title_default']))
						{
							
						}
						else
						{
							$titleUseDefault = 0;
						}
					}
					if($roomtypesModel->getRoomtypeTitleTransalte() != '')
					{
						$arCurrentTitle = $bkHelperText->getBkJsonDecode($roomtypesModel->getRoomtypeTitleTransalte());
					}
				}
			}
			$arTitleTrans = $bkHelperText->getTextTranslate($roomtypeTitle,$defaultTitle,$storeId,$isNew,$arCurrentTitle,$titleUseDefault);
			$formData['roomtype_title_transalte'] = $bkHelperText->getBkJsonEncode($arTitleTrans);
			$roomtypesModel->setData($formData);
			try {
				// Save facility
				$roomtypesModel->save();
	 
				// Display success message
				$this->messageManager->addSuccess(__('The room type has been saved.'));
	 
				// Check if 'Save and Continue'
				if ($this->getRequest()->getParam('back')) {
				   $this->_redirect('*/*/edit', ['id' => $roomtypesModel->getId(), '_current' => true, 'store'=>$storeId]);
				   return;
				}
	 
				// Go to grid page
				$this->_redirect('*/*/');
				return;
			} catch (\Exception $e) {
				$this->messageManager->addError($e->getMessage());
			}
				$this->_getSession()->setFormData($formData);
				$this->_redirect('*/*/edit', ['id' => $roomtypeId]);
		}
	}
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Magebay_Bookingsystem::add_facility');
	}
}
 