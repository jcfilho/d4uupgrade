<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Adminhtml\Payments\Edit\Tab;

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface {
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    protected $_newsCollection;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magebay\Marketplace\Model\ResourceModel\Payments\Collection $newsCollection,
		\Magento\Backend\Helper\Data $helper,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_newsCollection = $newsCollection;
        $this->_helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

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
        $isElementDisabled = !$this->_isAllowedAction('Magebay_Marketplace::manage_payments');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('payments_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('News Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

		$fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'fee',
            'text',
            [
                'name' => 'fee',
                'label' => __('Fee'),
                'title' => __('Fee'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'class' => 'validate-number',
            ]
        );
        $fieldset->addField(
            'minamount',
            'text',
            [
                'name' => 'minamount',
                'label' => __('Min amount'),
                'title' => __('Min amount'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'class' => 'validate-number',
                'note' => 'Minimum withdrawal amount. Leave 0 or blank for unlimited.'
            ]
        );
        $fieldset->addField(
            'maxamount',
            'text',
            [
                'name' => 'maxamount',
                'label' => __('Max amount'),
                'title' => __('Max amount'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'class' => 'validate-number',
                'note' => 'Maximum withdrawal amount. Leave 0 or blank for unlimited.'
            ]
        );
        $fieldset->addField(
            'sortorder',
            'text',
            [
                'name' => 'sortorder',
                'label' => __('Sort Order'),
                'title' => __('Sort Order'),
                'required' => true,
                'class' => 'validate-number',
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'description',
            'textarea',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'note',
            'textarea',
            [
                'name' => 'note',
                'label' => __('Note message'),
                'title' => __('Note message'),
                'disabled' => $isElementDisabled,
                'note' => 'This message will be display on the withdrawal page.'
            ]
        );
        $fieldset->addField(
            'email_account',
            'select',
            [
                'label' => __('Show Email Account '),
                'title' => __('Show Email Account '),
                'name' => 'email_account',
                'required' => true,
                'options' => $this->getOptionYesNo(),
                'disabled' => $isElementDisabled
            ]
        );   
        $fieldset->addField(
            'additional',
            'select',
            [
                'label' => __('Show Additional Information'),
                'title' => __('Show Additional Information'),
                'name' => 'additional',
                'required' => true,
                'options' => $this->getOptionYesNo(),
                'disabled' => $isElementDisabled
            ]
        );  
        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'required' => true,
                'options' => $this->getStatusYesNo(),
                'disabled' => $isElementDisabled
            ]
        );  
		
        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '0' : '1');
        }                       
        $this->_eventManager->dispatch('magebay_marketplace_payments_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
    
    public function getOptionYesNo()
    {
        return [1 => __('Yes'),0 => __('No')];
    }
    
    public function getStatusYesNo()
    {
        return [1 => __('Enable'),0 => __('Disable')];
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Main');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Main');
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