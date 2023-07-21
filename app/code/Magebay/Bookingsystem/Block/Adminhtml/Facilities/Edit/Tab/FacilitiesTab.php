<?php

namespace Magebay\Bookingsystem\Block\Adminhtml\Facilities\Edit\Tab;


use Magento\Backend\Block\Widget\Grid\Column;


class FacilitiesTab  extends \Magento\Backend\Block\Widget\Grid\Extended
{
	/**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    protected $_facilitiesFactory; 

    protected $_objectManager;

    protected $_status;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    //protected $_template = 'Magebay_Bookingsystem::facility/form/tabs_grid_js.phtml';

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\Product $facilitiesFactory,
        \Magebay\Bookingsystem\Model\System\Config\Status $status,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_facilitiesFactory = $facilitiesFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        $this->_objectManager = $objectManager;
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('listsGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('lists_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_facilitiesFactory->getCollection()
				->addAttributeToSelect(array('*'));
		//echo "<pre>";
		print_r($collection->getData());
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    protected function _addColumnFilterToCollection($column)
    {

    /* if ($column->getId() == 'in_lists_gridtabs') {
            $inlistsIds = $this->getAlists();

            if (empty($inlistsIds)) {
                $inlistsIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('id', ['in' => $inlistsIds]);
            } else {
                if ($inlistsIds) {
                    $this->getCollection()->addFieldToFilter('id', ['nin' => $inlistsIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        } */
        return $this;
    }
    public function getAlists($json = false)
    {
         //return [1];
        /* $id=$this->getRequest()->getParam('id');
        $collection = $this->_facilitiesFactory->create()->setId($id);
        $listso = $this->_objectManager->create('Magebay\Bookingsystem\Model\Facilities')->load($id);
        $collection = unserialize($listso->getInListsGrid()); echo 'Eeee';
		print_r($collection->getData()); return false;
        if (sizeof($collection) > 0) {
            if ($json) {
                $jsonLists = [];
                foreach ($collection as $usrid) {
                    $jsonLists[$usrid] = 0;
                }
                return $this->_jsonEncoder->encode((object)$jsonLists);
            } else {
                return array_values($collection);
            }
        } else {
            if ($json) {
                return '{}';
            } else {
                return [];
            }
        } */
		return '{}';

    }  


    public function isReadonly()
    {
        return true;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
         $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'type' => 'text',
                'index' => 'name',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'name'=>'name'
            ]
        );  
        $this->addColumn(
            'sku',
            [
                'header' => __('Sku'),
                'type' => 'text',
                'index' => 'name',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'name'=>'sku'
            ]
        );  
        return parent::_prepareColumns();
    }


    /**
     * @return string
     */
    public function getGridUrl()
    {
        $id = $this->getRequest()->getParam('id');
        return $this->getUrl('*/*/gridtabs', ['id' => $id]);
    }


    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Test Grid');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Test Grid');
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

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
		//return true;
    }
}