<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Facilities;
 
use Magebay\Bookingsystem\Controller\Adminhtml\Facilities;

class Save extends Facilities
{
   /**
     * @return void
     */
	public function execute()
	{
		$isPost = $this->getRequest()->getPost();
		$facilityId = 0;
		$isNew = true;
		$arCurrentTitle = array();
		$defaultTitle = '';
		$titleUseDefault = 1;
		$facilityTitle  = '';
		//des
		$arCurrentDes = array();
		$defaultDes = '';
		$desUseDefault = 1;
		$desTitle  = '';
		$storeId = 0;
		if ($isPost) 
		{
			$bkHelperText = $this->_bkHelperText;
			$formData = $this->getRequest()->getParam('facilities');
			$facilityTitle = isset($formData['facility_title']) ? $formData['facility_title'] : '';
			$desTitle = isset($formData['facility_description']) ? $formData['facility_description'] : '';
			$storeId = isset($formData['store_id']) ? $formData['store_id'] : 0;
			$facilitiesModel = $this->_facilitiesFactory->create();
			//$facilityId = $this->getRequest()->getParam('id',0);
			$facilityId = isset($formData['facility_id']) ? $formData['facility_id'] : 0;
			if ($facilityId) {
				$facilitiesModel->load($facilityId);
				if($facilitiesModel->getId())
				{
					$isNew = false;
					$defaultTitle = $facilitiesModel->getFacilityTitle();
					$defaultDes = $facilitiesModel->getFacilityDescription();
					if($storeId > 0)
					{
						unset($formData['facility_title']);
						if(isset($formData['title_default']))
						{
							
						}
						else
						{
							$titleUseDefault = 0;
						}
						//des
						unset($formData['facility_description']);
						if(isset($formData['des_default']))
						{
							
						}
						else
						{
							$desUseDefault = 0;
						}
					}
					if($facilitiesModel->getFacilityTitleTransalte() != '')
					{
						
						$arCurrentTitle = $bkHelperText->getBkJsonDecode($facilitiesModel->getFacilityTitleTransalte());
					}
					if($facilitiesModel->getFacilityDesTranslate() != '')
					{
						
						$arCurrentDes = $bkHelperText->getBkJsonDecode($facilitiesModel->getFacilityDesTranslate());
					}
				}
			}
			$arTitleTrans = $bkHelperText->getTextTranslate($facilityTitle,$defaultTitle,$storeId,$isNew,$arCurrentTitle,$titleUseDefault);
			$arDesTrans = $bkHelperText->getTextTranslate($desTitle,$defaultDes,$storeId,$isNew,$arCurrentDes,$desUseDefault);
			$formData['facility_title_transalte'] = $bkHelperText->getBkJsonEncode($arTitleTrans);
			$formData['facility_des_translate'] = $bkHelperText->getBkJsonEncode($arDesTrans);
			$formData['facility_image'] = $this->_uploadImages->uploadFileAndGetName('facility_image', $this->_imageModel->getBaseDir(), $formData);
			
			$formData['facility_booking_ids'] = '';
			if(isset($formData['booking_ids']) && count($formData['booking_ids']))
			{
				$formData['facility_booking_ids'] = implode(',',$formData['booking_ids']);
			}
			$facilitiesModel->setData($formData);
			try {
				// Save facility
				$facilitiesModel->save();
	 
				// Display success message
				$this->messageManager->addSuccess(__('The facility has been saved.'));
	 
				// Check if 'Save and Continue'
				if ($this->getRequest()->getParam('back')) {
				   $this->_redirect('*/*/edit', ['id' => $facilitiesModel->getId(), '_current' => true,'store'=>$storeId]);
				   return;
				}
	 
				// Go to grid page
				$this->_redirect('*/*/',['store'=>$storeId]);
				return;
			} catch (\Exception $e) {
				$this->messageManager->addError($e->getMessage());
			}
				$this->_getSession()->setFormData($formData);
				$this->_redirect('*/*/edit', ['id' => $facilityId]);
		}
	}
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Magebay_Bookingsystem::add_facility');
	}
}
 