<?php

namespace Magebay\Bookingsystem\Block\Adminhtml;
 
use Magento\Backend\Block\Widget\Grid\Container;
class Bookings extends Container
{
	/**
     * @param \Magento\Backend\Model\Auth\Session
     * 
    */
	protected $_backendSession;
	function __construct(
		\Magento\Backend\Block\Widget\Context $context,
		array $data = []
	)
	{
		parent::__construct($context, $data);
	}
   /**
     * Constructor
     *
     * @return void
     */
   protected function _construct()
    {
        $this->_controller = 'adminhtml_bookings';
        $this->_blockGroup = 'Magebay_Bookingsystem';
        $this->_headerText = __('Manage Booking system');
        $this->_addButtonLabel = __('Add News');
        parent::_construct();
		$this->removeButton('add');
    }
	/* 
	* get max options Id
	*/
	function getMaxOptionId()
	{
		$adminSession = $this->_backendSession;
		return $adminSession->getMaxOptionId();
	}
}