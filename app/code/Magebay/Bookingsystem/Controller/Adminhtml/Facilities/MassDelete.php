<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Facilities;
 
use Magebay\Bookingsystem\Controller\Adminhtml\Facilities;
 
class MassDelete extends Facilities
{
   /**
    * @return void
    */
	public function execute()
   {
      // Get IDs of the selected facilities
      $facilityIds = $this->getRequest()->getParam('facilities',array());
		if(count($facilityIds))
		{
			foreach ($facilityIds as $facilityId) {
				try {
				   /** @var $facilitiesModel \Magebay\Bookingsystem\Model\facilities */
					$facilitiesModel = $this->_facilitiesFactory->create();
					$facilitiesModel->load($facilityId)->delete();
				} catch (\Exception $e) {
					$this->messageManager->addError($e->getMessage());
				}
			}
		}
        
 
        if (count($facilityIds)) {
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) were deleted.', count($facilityIds))
            );
        }
 
        $this->_redirect('*/*/index');
	}
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Magebay_Bookingsystem::add_facility');
	}
}