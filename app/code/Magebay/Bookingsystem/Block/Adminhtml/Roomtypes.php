<?php

namespace Magebay\Bookingsystem\Block\Adminhtml;
 
use Magento\Backend\Block\Widget\Grid\Container;

class Roomtypes extends Container
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
        $this->_controller = 'adminhtml_roomtypes';
        $this->_blockGroup = 'Magebay_Bookingsystem';
        $this->_headerText = __('Manage News');
        $this->_addButtonLabel = __('Add News');
        parent::_construct();
    }
}