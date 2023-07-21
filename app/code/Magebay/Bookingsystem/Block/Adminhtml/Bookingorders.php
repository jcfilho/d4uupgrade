<?php

namespace Magebay\Bookingsystem\Block\Adminhtml;
 
use Magento\Backend\Block\Widget\Grid\Container;

class Bookingorders extends Container
{
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
        $this->_controller = 'adminhtml_bookingorders';
        $this->_blockGroup = 'Magebay_Bookingsystem';
        $this->_headerText = __('Booking Lists');
        parent::_construct();
		$this->removeButton('add');
    }
}