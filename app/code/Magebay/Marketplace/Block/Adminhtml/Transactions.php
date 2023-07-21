<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/

namespace Magebay\Marketplace\Block\Adminhtml;

class Transactions extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'transactions/grid.phtml';
    protected $_customerCollectionFactory;
    protected $_transactionsCollectionFactory;
    protected $_objectmanager;
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magebay\Marketplace\Model\TransactionsFactory $transactionsFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->_customerCollectionFactory = $customerFactory;	
        $this->_transactionsCollectionFactory = $transactionsFactory;	
        $this->_objectmanager = $objectmanager;
        parent::__construct($context, $data);
    }
 
    /**
     * Prepare button and Create transactions , edit/add transactions row and installer in Magento2
     *
     * @return \Magento\Catalog\Block\Adminhtml\Transactions
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'transactions',
            $this->getLayout()->createBlock('Magebay\Marketplace\Block\Adminhtml\Transactions\Grid', 'transactions.view.grid')
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
            'transactions/*/new'
        );
    }
 
    /**
     * Render transactions
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('transactions');
    }
    
    public function getBackUrl(){
        $url = $this->_objectmanager->create('Magento\Backend\Helper\Data')->getUrl('marketplace/transactions/index', array(''));
        return $url;
    }
}