<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Bookings;
 
use Magebay\Bookingsystem\Controller\Adminhtml\Bookings;
 
class Grid extends Bookings
{
   /**
     * @return void
     */
   public function execute()
   {
      return $this->_resultPageFactory->create();
   }
}
 