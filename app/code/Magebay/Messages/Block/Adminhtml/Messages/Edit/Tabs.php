<?php
namespace Magebay\Messages\Block\Adminhtml\Messages\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('messages_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Messages Information'));
    }
    
	/* extra */
	protected function _beforeToHtml()
	{
		$this->addTab(
			'messages_reply',
			[
				'label' => __('Messages'),
				'url' => $this->getUrl('magebay/messages/reply', ['_current' => true]),
				'class' => 'ajax'
			]
		);
		return parent::_beforeToHtml();
	}
	/* extra */	
}