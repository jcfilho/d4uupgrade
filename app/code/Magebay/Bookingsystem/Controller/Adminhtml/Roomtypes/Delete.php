<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Roomtypes;
 
use Magebay\Bookingsystem\Controller\Adminhtml\Roomtypes;
 
class Delete extends Roomtypes
{
   /**
    * @return void
    */
	public function execute()
	{
      $roomtypeId = (int) $this->getRequest()->getParam('id');
 
      if ($roomtypeId) {
         /** @var $roomtypesModel \Magebay\Bookingsystem\Model\Roomtypes */
         $roomtypesModel = $this->_roomtypesFactory->create();
         $roomtypesModel->load($facilityId);
 
         // Check this news exists or not
         if (!$roomtypesModel->getId()) {
            $this->messageManager->addError(__('This news no longer exists.'));
         } else {
               try {
                  // Delete news
                  $roomtypesModel->delete();
                  $this->messageManager->addSuccess(__('The news has been deleted.'));
 
                  // Redirect to grid page
                  $this->_redirect('*/*/');
                  return;
               } catch (\Exception $e) {
                   $this->messageManager->addError($e->getMessage());
                   $this->_redirect('*/*/edit', ['id' => $roomtypesModel->getId()]);
               }
            }
      }
	}
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Magebay_Bookingsystem::add_facility');
	}
}