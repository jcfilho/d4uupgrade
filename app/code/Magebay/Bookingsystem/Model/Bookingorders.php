<?php
 
namespace Magebay\Bookingsystem\Model;
 
use Magento\Framework\Model\AbstractModel;
 
class Bookingorders extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Magebay\Bookingsystem\Model\ResourceModel\Bookingorders');
    }
	function deleteBkOrders($bookingId,$roomId = 0)
	{
		$collection = $this->getCollection()
			->addFieldToFilter('bkorder_booking_id',$bookingId)
			->addFieldToFilter('bkorder_room_id',$roomId);
		if(count($collection))
		{
			foreach($collection as $collect)
			{
				$this->setId($collect->getId())->delete();
			}
		}
	}
}