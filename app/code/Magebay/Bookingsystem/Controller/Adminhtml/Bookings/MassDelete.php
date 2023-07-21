<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Bookings;
 
use Magebay\Bookingsystem\Controller\Adminhtml\Bookings;

class MassDelete extends Bookings
{
   /**
    * @return void
    */
	public function execute()
   {
      // Get IDs of the selected Bookings
	  
      $bookingIds = $this->getRequest()->getParam('bookings');
        foreach ($bookingIds as $bookingId) {
            try {
                
				$bookings = $this->_bookingFactory->create();
				$bookings->deleteBookings($bookingId);
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
 
        if (count($bookingIds)) {
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) were deleted.', count($bookingIds))
            );
        }
 
        $this->_redirect('*/*/index');
	}
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Magebay_Bookingsystem::manage_bookings');
	}
}