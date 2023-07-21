<?php
 
namespace Magebay\Bookingsystem\Block\Adminhtml\Facilities\Edit\Tab;
 
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config;
use Magebay\Bookingsystem\Model\System\Config\Status;
use Magebay\Bookingsystem\Model\System\Config\BookingType;
 
class Info extends Generic implements TabInterface
{
	//protected $_template = 'Magebay_Bookingsystem::widget/form.phtml';
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;
 
    /**
     * @var \Magebay\Bookingsystem\Model\Config\Status
     */
    protected $_status;
	  /**
     * @var \Magento\Framework\App\RequestInterface
     */
	//protected $_request;
	 /**
     * @var \Magebay\Bookingsystem\Model\Config\BookingType
     */
    protected $_bookingType;
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
        BookingType $bookingType,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_status = $status;
        $this->_bookingType = $bookingType;
        parent::__construct($context, $registry, $formFactory, $data);
    }
 
    /**
     * Prepare form fields
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
       /** @var $model \Magebay\Bookingsystem\Model\Facilities */
        $model = $this->_coreRegistry->registry('facilities_data');
 
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('facilities_');
        $form->setFieldNameSuffix('facilities');
 
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General')]
        );
        if ($model->getId()) {
            $fieldset->addField(
                'facility_id',
                'hidden',
                ['name' => 'facility_id']
            );
        }
		$storeId = $this->_request->getParam('store',0);
		$fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'store_id']
            );
		$field = $fieldset->addField(
		   'facility_title',
		   'text',
		   [
			   'name' 	=> 'facility_title',
			   'title'	=> __('Title'),
		   ]
		);
		$fieldTitle = $this->getLayout()->createBlock(
		   'Magebay\Bookingsystem\Block\Adminhtml\Facilities\Edit\From\FacilityTilte'
		); 
		$field->setRenderer($fieldTitle);
        $fieldset->addField(
            'facility_status',
            'select',
            [
                'name'      => 'facility_status',
                'label'     => __('Status'),
                'options'   => $this->_status->toOptionArray()
            ]
        );
		$field = $fieldset->addField(
		   'facility_icon_class',
		   'text',
		   [
			   'name' 	=> 'facility_icon_class',
			   'title'	=> __('Use font icon instead image thumbnail?'),
		   ]
		);
		$fieldIcon = $this->getLayout()->createBlock(
		   'Magebay\Bookingsystem\Block\Adminhtml\Facilities\Edit\From\IconClass'
		); 
		$field->setRenderer($fieldIcon);
		 $fieldset->addField(
            'facility_position',
            'text',
            [
                'name'        => 'facility_position',
                'label'    => __('Position'),
                'required'     => true
            ]
        );
		$fieldset->addField(
            'facility_booking_type',
			'select',
            [
                'name'      => 'facility_booking_type',
                'label'     => __('Booking Type'),
                'options'   => $this->_bookingType->toOptionArray()
            ]
        );
		$field = $fieldset->addField(
		   'facility_booking_ids',
		   'text',
		   [
			   'name' 	=> 'facility_booking_ids',
			   'title'	=> __('Booking '),
		   ]
		);
		$fieldFacilities = $this->getLayout()->createBlock(
		   'Magebay\Bookingsystem\Block\Adminhtml\Facilities\Edit\From\FacilityBookingIds'
		); 
		$field->setRenderer($fieldFacilities);
		$fieldset->addField(
			'facility_image',
			'image',
			array(
				'name' => 'facility_image',
				'label' => __('Image'),
				'title' => __('Image')
			)
		);

		$field = $fieldset->addField(
		   'facility_description',
		   'text',
		   [
			   'name' 	=> 'facility_description',
			   'title'	=> __('Description'),
		   ]
		);
		$fieldDes = $this->getLayout()->createBlock(
		   'Magebay\Bookingsystem\Block\Adminhtml\Facilities\Edit\From\FacilityDes'
		); 
		$field->setRenderer($fieldDes);
        $data = $model->getData();
		if(isset($data['facility_image']) && $data['facility_image'] != '')
		{
			$data['facility_image'] = 'bookingsystem/image/'.$data['facility_image'];
		}
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