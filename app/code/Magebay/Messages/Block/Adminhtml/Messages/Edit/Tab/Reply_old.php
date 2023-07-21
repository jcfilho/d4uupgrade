<?php
/**
 * @Author      : David
 * @package     Messages
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/

namespace Magebay\Messages\Block\Adminhtml\Messages\Edit\Tab;

/**
 * Admin blog post edit form reply tab
 */
class Reply extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_model');

        /*
         * Checking if user have permissions to save information
         */
        $isElementDisabled = !$this->_isAllowedAction('Magebay_Messages::manage_sellers');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('sellers_');

        $fieldset = $form->addFieldset(
            'reply_fieldset',
            ['legend' => __('Messages Reply'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'replysource',
            'textarea',
            [
                'name' => 'replysource',
                'label' => __('Reply Info'),
                'title' => __('Reply Info'),
                'disabled' => $isElementDisabled
            ]
        );

        $this->_eventManager->dispatch('magebay_messages_messages_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Messages Reply');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Messages Reply');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}