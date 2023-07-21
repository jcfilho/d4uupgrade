<?php
/**
 * @Author      : David
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Adminhtml\Sellers\Edit\Tab;

/**
 * Admin blog post edit form meta tab
 */
class Products extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
        $isElementDisabled = !$this->_isAllowedAction('Magebay_Marketplace::manage_sellers');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('sellers_');

        $fieldset = $form->addFieldset(
            'meta_fieldset',
            ['legend' => __('List Products'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'meta_keywords',
            'textarea',
            [
                'name' => 'post[meta_keywords]',
                'label' => __('Keywords'),
                'title' => __('Meta Keywords'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'meta_description',
            'textarea',
            [
                'name' => 'post[meta_description]',
                'label' => __('Description'),
                'title' => __('Meta Description'),
                'disabled' => $isElementDisabled
            ]
        );

        $this->_eventManager->dispatch('magebay_marketplace_sellers_edit_tab_main_prepare_form', ['form' => $form]);

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
        return __('List Products');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('List Products');
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
