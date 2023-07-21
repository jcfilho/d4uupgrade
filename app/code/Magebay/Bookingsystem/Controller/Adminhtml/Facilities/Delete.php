<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Facilities;
 
use Magebay\Bookingsystem\Controller\Adminhtml\Facilities;
 
class Delete extends Facilities
{
   /**
    * @return void
    */
	public function execute()
	{
      $facilityId = (int) $this->getRequest()->getParam('id');
 
      if ($facilityId) {
         /** @var $FacilitiesModel \Magebay\Bookingsystem\Model\Facilities */
         $facilitiesModel = $this->_facilitiesFactory->create();
         $facilitiesModel->load($facilityId);
 
         // Check this news exists or not
         if (!$facilitiesModel->getId()) {
            $this->messageManager->addError(__('This news no longer exists.'));
         } else {
               try {
                  // Delete news
                  $facilitiesModel->delete();
                  $this->messageManager->addSuccess(__('The news has been deleted.'));
 
                  // Redirect to grid page
                  $this->_redirect('*/*/');
                  return;
               } catch (\Exception $e) {
                   $this->messageManager->addError($e->getMessage());
                   $this->_redirect('*/*/edit', ['id' => $facilitiesModel->getId()]);
               }
            }
      }
	}
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Magebay_Bookingsystem::add_facility');
	}
}