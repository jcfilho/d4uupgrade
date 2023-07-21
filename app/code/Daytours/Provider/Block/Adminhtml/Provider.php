<?php

namespace Daytours\Provider\Block\Adminhtml;

/**
 * Adminhtml cms blocks content block
 */
class Provider extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Daytours_Provider';
        $this->_controller = 'adminhtml_provider';
        $this->_headerText = __('Providers');
        $this->_addButtonLabel = __('Add New Provider');
        parent::_construct();
    }
}
