<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/

namespace Magebay\Marketplace\Block\Adminhtml;

class Payments extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'payments/grid.phtml';
    protected $_customerCollectionFactory;
    protected $_paymentsCollectionFactory;
    protected $_objectmanager;
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magebay\Marketplace\Model\PaymentsFactory $paymentsFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->_customerCollectionFactory = $customerFactory;	
        $this->_paymentsCollectionFactory = $paymentsFactory;	
        $this->_objectmanager = $objectmanager;
        parent::__construct($context, $data);
    }
 
    /**
     * Prepare button and Create payments , edit/add payments row and installer in Magento2
     *
     * @return \Magento\Catalog\Block\Adminhtml\Payments
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'payments',
            $this->getLayout()->createBlock('Magebay\Marketplace\Block\Adminhtml\Payments\Grid', 'payments.view.grid')
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
            'payments/*/new'
        );
    }
 
    /**
     * Render payments
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('payments');
    }
}