<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Facilities;
 
use Magebay\Bookingsystem\Controller\Adminhtml\Facilities;
 
class Edit extends Facilities
{
	/**
     * @return void
     */
	public function execute()
   {
      $facilityId = $this->getRequest()->getParam('id');
        /** @var \Magebay\Bookingsystem\Model\Facilities $model */
        $model = $this->_facilitiesFactory->create();
 
        if ($facilityId) {
            $model->load($facilityId);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This news no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }
 
        // Restore previously entered form data from session
        $data = $this->_session->getNewsData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $this->_coreRegistry->register('facilities_data', $model); 
 
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Magebay_Bookingsystem::booking_menu');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Facilities'));
 
        return $resultPage;
	}
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Magebay_Bookingsystem::add_facility');
	}
}
 