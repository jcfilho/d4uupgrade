<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Adminhtml;

class Partner extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'partner/grid.phtml';
    protected $_customerCollectionFactory;
    protected $_partnerCollectionFactory;
    protected $_objectmanager;
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magebay\Marketplace\Model\PartnerFactory $partnerFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->_customerCollectionFactory = $customerFactory;	
        $this->_partnerCollectionFactory = $partnerFactory;	
        $this->_objectmanager = $objectmanager;
        parent::__construct($context, $data);
    }
 
    /**
     * Prepare button and Create partner , edit/add partner row and installer in Magento2
     *
     * @return \Magento\Catalog\Block\Adminhtml\Partner
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'partner',
            $this->getLayout()->createBlock('Magebay\Marketplace\Block\Adminhtml\Partner\Grid', 'partner.view.grid')
        );
        return parent::_prepareLayout();
    }
                
    /**
     *
     *
     * @param string $type
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl(
            'partner/*/new'
        );
    }
 
    /**
     * Render partner
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('partner');
    }
}