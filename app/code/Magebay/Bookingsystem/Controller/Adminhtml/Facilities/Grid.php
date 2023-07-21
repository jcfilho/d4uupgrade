<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Facilities;
 
use Magebay\Bookingsystem\Controller\Adminhtml\Facilities;
 
class Grid extends Facilities
{
   /**
     * @return void
     */
   public function execute()
   {
      return $this->_resultPageFactory->create();
   }
}
 