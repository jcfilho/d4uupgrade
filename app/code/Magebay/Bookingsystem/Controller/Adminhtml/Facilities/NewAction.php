<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Facilities;
 
use Magebay\Bookingsystem\Controller\Adminhtml\Facilities;
 
class NewAction extends Facilities
{
   /**
     * Create new news action
     *
     * @return void
     */
	public function execute()
	{
      $this->_forward('edit');
	}
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Magebay_Bookingsystem::add_facility');
	}
}
 