<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Adminhtml\Grid\Column;
use \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
class PartnerGridSellerEmail extends AbstractRenderer
{
    protected $_customerCollectionFactory;
    protected $_objectmanager;
    
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->_customerCollectionFactory = $customerFactory;	
        $this->_objectmanager = $objectmanager;
    }
    
    public function render(\Magento\Framework\DataObject $row)
    {
        $customer = $this->_customerCollectionFactory->create()->load($row->getSellerid());
        $cell = '<div class="data-grid-cell-content">'.$customer->getEmail().'</div>';
        return $cell;
    }
}