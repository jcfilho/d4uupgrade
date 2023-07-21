<?php
 
namespace Magebay\Bookingsystem\Block\Adminhtml\Roomtypes\Edit;
 
use Magento\Backend\Block\Widget\Tabs as WidgetTabs;
 
class Tabs extends WidgetTabs
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('roomtype_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Roomtype Infomation'));
    }
 
    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'facility_info',
            [
                'label' => __('General'),
                'title' => __('General'),
                'content' => $this->getLayout()->createBlock(
                    'Magebay\Bookingsystem\Block\Adminhtml\Roomtypes\Edit\Tab\Info'
                )->toHtml(),
                'active' => true
            ]
        );
 
        return parent::_beforeToHtml();
    }
}
 