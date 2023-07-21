<?php
 
namespace Magebay\Bookingsystem\Block\Adminhtml\Roomtypes\Edit\Tab;
 
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config;
use Magebay\Bookingsystem\Model\System\Config\Status;
 
class Info extends Generic implements TabInterface
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;
 
    /**
     * @var \Magebay\Bookingsystem\Model\Config\Status
     */
    protected $_status;
   /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Config $wysiwygConfig
     * @param Status $status
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        Status $status,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_status = $status;
        parent::__construct($context, $registry, $formFactory, $data);
    }
 
    /**
     * Prepare form fields
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
       /** @var $model \Magebay\Bookingsystem\Model\Roomtypes */
        $model = $this->_coreRegistry->registry('roomtype_data');
 
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('roomtypes_');
        $form->setFieldNameSuffix('roomtypes');
 
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General')]
        );
 
        if ($model->getId()) {
            $fieldset->addField(
                'roomtype_id',
                'hidden',
                ['name' => 'roomtype_id']
            );
        }
		$storeId = $this->_request->getParam('store',0);
		$fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'store_id']
            );
        /*  $fieldset->addField(
            'roomtype_title',
            'text',
            [
                'name'        => 'roomtype_title',
                'label'    => __('Title'),
                'required'     => true
            ]
        );  */
		$field = $fieldset->addField(
		   'roomtype_title',
		   'text',
		   [
			   'name' 	=> 'roomtype_title',
			   'title'	=> __('Title'),
		   ]
		);
		$fieldTitle = $this->getLayout()->createBlock(
		   'Magebay\Bookingsystem\Block\Adminhtml\Roomtypes\Edit\From\Tilte'
		);
		$field->setRenderer($fieldTitle);
        $fieldset->addField(
            'roomtype_status',
            'select',
            [
                'name'      => 'roomtype_status',
                'label'     => __('Status'),
                'options'   => $this->_status->toOptionArray()
            ]
        );
        $fieldset->addField(
            'roomtype_position',
            'text',
            [
                'name'      => 'roomtype_position',
                'label'     => __('Position')
            ]
        );
		$data = $model->getData();
		$data['store_id'] = $storeId;
        $form->setValues($data);
        $this->setForm($form);
 
        return parent::_prepareForm();
    }
 
    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('News Info');
    }
 
    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('News Info');
    }
 
    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }
 
    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}