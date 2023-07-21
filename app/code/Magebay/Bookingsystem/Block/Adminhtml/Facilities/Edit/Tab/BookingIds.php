<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebay\Bookingsystem\Block\Adminhtml\Facilities\Edit\Tab;

/**
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class BookingIds extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * User model factory
     *
     * @var \Magento\User\Model\Resource\User\CollectionFactory
     */


    protected $_facilitiesFactory; 

    protected $_objectManager;
	 protected $_template = 'Magebay_Bookingsystem::facility/form/tabs_grid_js.phtml';
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\User\Model\Resource\User\CollectionFactory $userCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $id = $this->getRequest()->getParam('id', false);
		//$this->setTemplate('Magebay_Bookingsystem::facility/form/tabs_grid_js.phtml');
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'listsGrid',
            $this->getLayout()->createBlock('Magebay\Bookingsystem\Block\Adminhtml\Facilities\Edit\Tab\FacilitiesTab', 'listsTabsGrid')
        );
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('listsGrid');
    }

}