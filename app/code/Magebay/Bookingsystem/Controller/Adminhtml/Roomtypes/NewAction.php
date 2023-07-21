<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Roomtypes;
 
use Magebay\Bookingsystem\Controller\Adminhtml\Roomtypes;
 
class NewAction extends Roomtypes
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
		return $this->_authorization->isAllowed('Magebay_Bookingsystem::add_roomtype');
	}
}
 