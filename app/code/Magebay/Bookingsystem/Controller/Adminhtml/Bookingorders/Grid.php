<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Bookingorders;
 
use Magebay\Bookingsystem\Controller\Adminhtml\Bookingorders;
 
class Grid extends Bookingorders
{
   /**
     * @return void
     */
   public function execute()
   {
      return $this->_resultPageFactory->create();
   }
}
 