<?php
namespace Magebay\Marketplace\Block\Adminhtml\Sellers\Edit\Tab;

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface {
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    protected $_sellersCollection;

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
        \Magebay\Marketplace\Model\ResourceModel\Sellers\Collection $sellersCollection,
		\Magento\Backend\Helper\Data $helper,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_sellersCollection = $sellersCollection;
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
        $isElementDisabled = !$this->_isAllowedAction('Magebay_Marketplace::manage_sellers');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('sellers_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Seller Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
		if ($model->getUserId()) {
    		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    		$customer = $objectManager->create('Magento\Customer\Model\Customer')->load( $model->getUserId() );
    		$customer_name = $customer->getData('firstname') . ' ' . $customer->getData('lastname');
    		$router = 'customer/index/edit/id/'.$model->getUserId();
    		$router_admin = $this->_helper->getUrl($router, $param = [] );
    		$note = '<a target="_blank" href="'.$router_admin.'" style="margin-top: 10px; float: left;">'.$customer_name.'</a>';
    		$fieldset->addField(
                'customer_account',
                'note',
                [
                    'label' => __('Customer Account'),
                    'title' => __('Customer Account'),
                    'required' => false,
                    'after_element_html' => $note,
                ]
            ); 
		}
		$fieldset->addField(
            'storetitle',
            'text',
            [
                'name' => 'storetitle',
                'label' => __('Store Title'),
                'title' => __('Store Title'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'storeurl',
            'text',
            [
                'name' => 'storeurl',
                'label' => __('Store Url'),
                'title' => __('Store Url'),
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'userstatus',
            'select',
            [
                'label' => __('Seller Status'),
                'title' => __('Seller Status'),
                'name' => 'userstatus',
                'required' => true,
                'options' => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );   
        $fieldset->addField(
            'logoimg',
            'image',
            [
                'title' => __('Company Logo'),
                'label' => __('Company Logo'),
                'name' => 'logoimg',
                'note' => __('Allow image type: jpg, jpeg, gif, png'),
            ]
        ); 
		$fieldset->addField(
            'bannerimg',
            'image',
            [
                'title' => __('Company Banner'),
                'label' => __('Company Banner'),
                'name' => 'bannerimg',
                'note' => __('Allow image type: jpg, jpeg, gif, png'),
            ]
        );    
        $fieldset->addField(
            'description',
            'editor',
            [
                'name' => 'description',
                'label' => __('Company Description'),
                'title' => __('Company Description'),
                'disabled' => $isElementDisabled,
                'wysiwyg'   => true                
            ]
        );
		$fieldset->addField(
            'meta_keyword',
            'text',
            [
                'name' => 'meta_keyword',
                'label' => __('Meta Keywords'),
                'title' => __('Meta Keywords'),
                'disabled' => $isElementDisabled
            ]
        );
		$fieldset->addField(
            'meta_description',
            'text',
            [
                'name' => 'meta_description',
                'label' => __('Meta Description'),
                'title' => __('Meta Description'),
                'disabled' => $isElementDisabled
            ]
        );
		/* $fieldset->addField(
            'meta_description',
            'textarea',
            [
                'name' => 'meta_description',
                'label' => __('Meta Description'),
                'title' => __('Meta Description'),
                'disabled' => $isElementDisabled
            ]
        ); */        
		$fieldset->addField(
            'contactnumber',
            'text',
            [
                'name' => 'contactnumber',
                'label' => __('Contact number'),
                'title' => __('Contact number'),
                'disabled' => $isElementDisabled
            ]
        );
		$fieldset->addField(
            'returnpolicy',
            'textarea',
            [
                'name' => 'returnpolicy',
                'label' => __('Return Policy'),
                'title' => __('Return Policy'),
                'disabled' => $isElementDisabled
            ]
        );
		$fieldset->addField(
            'shippingpolicy',
            'textarea',
            [
                'name' => 'shippingpolicy',
                'label' => __('Shipping Policy'),
                'title' => __('Shipping Policy'),
                'disabled' => $isElementDisabled
            ]
        );
		$fieldset->addField(
            'company',
            'text',
            [
                'name' => 'company',
                'label' => __('Company'),
                'title' => __('Company'),
                'disabled' => $isElementDisabled
            ]
        );   
		$fieldset->addField(
            'address',
            'text',
            [
                'name' => 'address',
                'label' => __('Address'),
                'title' => __('Address'),
                'disabled' => $isElementDisabled
            ]
        );
		$fieldset->addField(
            'city',
            'text',
            [
                'name' => 'city',
                'label' => __('City'),
                'title' => __('City'),
                'disabled' => $isElementDisabled
            ]
        );
		$fieldset->addField(
            'zipcode',
            'text',
            [
                'name' => 'zipcode',
                'label' => __('Zip code'),
                'title' => __('Zip code'),
                'disabled' => $isElementDisabled
            ]
        );
		$fieldset->addField(
            'country',
            'text',
            [
                'name' => 'country',
                'label' => __('Country'),
                'title' => __('Cipcode'),
                'disabled' => $isElementDisabled
            ]
        );
	   $fieldset->addField(
            'state',
            'text',
            [
                'name' => 'state',
                'label' => __('State'),
                'title' => __('State'),
                'disabled' => $isElementDisabled
            ]
        );
		$fieldset->addField(
            'facebookid',
            'text',
            [
                'name' => 'facebookid',
                'label' => __('Facebook Id'),
                'title' => __('Facebook Id'),
                'disabled' => $isElementDisabled
            ]
        );
		$fieldset->addField(
            'twitterid',
            'text',
            [
                'name' => 'twitterid',
                'label' => __('Twitter Id'),
                'title' => __('Twitter Id'),
                'disabled' => $isElementDisabled
            ]
        );
		
        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '0' : '1');
        }                       
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
        return __('Seller Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Seller Information');
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