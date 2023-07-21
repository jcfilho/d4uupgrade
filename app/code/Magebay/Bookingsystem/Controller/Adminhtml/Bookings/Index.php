<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Bookings;
 
use Magebay\Bookingsystem\Controller\Adminhtml\Bookings;
 
class Index extends Bookings
{
    /**
     * @return void
     */
	public function execute()
   {
      if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Magebay_Bookingsystem::booking_menu');
        $resultPage->getConfig()->getTitle()->prepend(__('Booking system - Magage Items'));
		$model = $this->_bookingFactory->create();
        return $resultPage;
	}
	
}