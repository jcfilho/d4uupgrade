<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Roomtypes;
 
use Magebay\Bookingsystem\Controller\Adminhtml\Roomtypes;
 
class Edit extends Roomtypes
{
	/**
     * @return void
     */
	public function execute()
   {
      $roomtypeId = $this->getRequest()->getParam('id');
        /** @var \Magebay\Bookingsystem\Model\Roomtypes $model */
        $model = $this->_roomtypesFactory->create();
 
        if ($roomtypeId) {
            $model->load($roomtypeId);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This news no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }
 
        // Restore previously entered form data from session
        $data = $this->_session->getRoomtypeData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $this->_coreRegistry->register('roomtype_data', $model);
 
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Magebay_Dream::booking_menu');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Room Types'));
 
        return $resultPage;
	}
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Magebay_Bookingsystem::add_roomtype');
	}
}
 