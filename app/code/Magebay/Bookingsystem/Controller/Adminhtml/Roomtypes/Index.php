<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Roomtypes;
 
use Magebay\Bookingsystem\Controller\Adminhtml\Roomtypes;
 
class Index extends Roomtypes
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
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Room Types'));
 
        return $resultPage;
	}
	
}