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
class PartnerGridSellerCommission extends AbstractRenderer
{
    protected $_sellerCollectionFactory;
    protected $_objectmanager;
    
    public function __construct(
        \Magebay\Marketplace\Model\SellersFactory $sellersFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {	
        $this->_sellerCollectionFactory = $sellersFactory;	
        $this->_objectmanager = $objectmanager;
    }
    
    public function render(\Magento\Framework\DataObject $row)
    {
        $seller = $this->_sellerCollectionFactory->create()->getCollection()->addFieldToFilter('user_id',$row->getSellerid())->getFirstItem();
        if($seller['commission']){
            $url = $this->_objectmanager->create('Magento\Backend\Helper\Data')->getUrl('marketplace/sellers/edit', array('id'=>$seller['id']));
            $cell = '<a title="View Customer" href="'.$url.'">'.$seller['commission'].'</a>';
        }else{
            $url = $this->_objectmanager->create('Magento\Backend\Helper\Data')->getUrl('marketplace/sellers/edit', array('id'=>$seller['id']));
            $cell = '<a title="View Customer" href="'.$url.'">Set</a>';
        }
        return $cell;
    }
}