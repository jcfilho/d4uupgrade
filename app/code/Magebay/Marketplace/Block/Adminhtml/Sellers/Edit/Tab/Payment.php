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
 * Admin blog post edit form payment tab
 */
class Payment extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
            'payment_fieldset',
            ['legend' => __('Paypal Adapvite Payment Data'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'paymentsource',
            'textarea',
            [
                'name' => 'paymentsource',
                'label' => __('Paypal Email Info'),
                'title' => __('Paypal Email Info'),
                'class' => 'validate-email',
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
        return __('Payment Data');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Payment Data');
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