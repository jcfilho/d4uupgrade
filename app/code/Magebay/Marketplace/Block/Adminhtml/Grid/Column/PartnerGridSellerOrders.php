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
class PartnerGridSellerOrders extends AbstractRenderer
{
    protected $_objectmanager;
    
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->_objectmanager = $objectmanager;
    }
    
    public function render(\Magento\Framework\DataObject $row)
    {
        $url = $this->_objectmanager->create('Magento\Backend\Helper\Data')->getUrl('marketplace/orders/index', array('sellerid'=>$row->getSellerid()));
        $cell = '<a title="View Orders" href="'.$url.'">Orders</a>';
        return $cell;
    }
}