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
class ProductsGridSellerName extends AbstractRenderer
{
    protected $_customerCollectionFactory;
    protected $_product;
    protected $_objectmanager;
    
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->_customerCollectionFactory = $customerFactory;	
        $this->_product = $product;
        $this->_objectmanager = $objectmanager;
    }
    
    public function render(\Magento\Framework\DataObject $row)
    {
        $customer = $this->_customerCollectionFactory->create()->load($row->getUserId());
  		$sellerCollection = $this->_objectmanager->create('Magebay\Marketplace\Model\ResourceModel\Sellers\Collection')->addFieldToFilter('user_id',$row->getUserId())->getFirstItem();
        $url = $this->_objectmanager->create('Magento\Backend\Helper\Data')->getUrl('marketplace/sellers/edit', array('id'=>$sellerCollection['id']));
        $cell = '<a title="View Customer" href="'.$url.'">'.$customer->getName().'</a>';
        return $cell;
    }
}