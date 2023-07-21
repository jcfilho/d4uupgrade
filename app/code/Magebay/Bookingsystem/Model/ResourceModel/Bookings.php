<?php
 
namespace Magebay\Bookingsystem\Model\ResourceModel;
 
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
 
class Bookings extends AbstractDb
{
	 /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $connectionName = null
    ) {
        $this->_eventManager = $eventManager;
        parent::__construct($context, $connectionName);
    }
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('booking_systems', 'booking_id');
    }
	/* function deleteBookings($bookingId,$changeType)
	{
		$this->_eventManager->dispatch('bookingsystem_delete_bookings',array('booking_id' => $bookingId,'change_type'=>$changeType));
        return $this;
	} */
}