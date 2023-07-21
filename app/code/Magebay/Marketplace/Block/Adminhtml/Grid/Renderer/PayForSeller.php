<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Adminhtml\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Framework\Registry;
 
class PayForSeller extends AbstractRenderer
{
    /**
     * @var Registry
     */
    protected $registry;
    
    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;
    
    /**
     * Manufacturer constructor.
     * @param AttributeFactory $attributeFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $registry,
        AttributeFactory $attributeFactory,
        Context $context,
        array $data = array()
    )
    {
        $this->attributeFactory = $attributeFactory;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }
 
    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return mixed
     */
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        $saleOrderId = parent::_getValue($row);
        /*$item_invoice = 0;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$invoice = $objectManager->create('Magento\Sales\Model\Order\Invoice')->getCollection()->addAttributeToSelect('*')
                                                                                ->addAttributeToFilter('order_item_id', array('eq' => $item->getItemId()))
                                                                                ->addAttributeToFilter('product_id', array('eq' => $item->getProdid()))
                                                                                ->load();
        foreach ($invoice as $value) {
            $item_invoice = 1;
        }
        
		$total_amount = Mage::helper('core')->currency( $item->getActualparterprocost() , true, false); 
        if(($item->getPaidstatus() == 0) && ($item->getOrprostatus() == 1) && ($item->getStatus()=='complete'||$item_invoice)){
            $item->payseller = '<button type="button" class="button mst_payseller" total_amount="'.$total_amount.'" auto-id="'.$item->getItemid().'" title="'.Mage::helper('multivendor')->__('Pay Item').'"><span><span><span>'.Mage::helper('multivendor')->__('Pay Item').'</span></span></span></button>';
        }else if(($item->getPaidstatus() == 0||$item->getPaidstatus() == 4) && ($item->getOrprostatus() == 0)){
            $item->payseller = Mage::helper('multivendor')->__('Order Pending');
        }else if(($item->getPaidstatus() == 0 || $item->getPaidstatus() == 4 || $item->getPaidstatus() == 2) && ($item->getOrprostatus() == 1) && ($item->getStatus() != 'complete')){
            $item->payseller = Mage::helper('multivendor')->__('Order Pending');
        }else{
            if(strpos($item->getStatus(),'cancel') !== false && $item->getPaidstatus() == 2){
                $item->payseller = Mage::helper('multivendor')->__('Order Cancelled');
            }else{
                $item->payseller = Mage::helper('multivendor')->__('Already Paid');
            }
        }*/
        /*$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $dataSaleOrder = $objectManager->create('Magebay\Marketplace\Model\Saleslist')->load($saleOrderId);
        $html = '';
        if(($dataSaleOrder->getOrderStatus() == 'complete') && ($dataSaleOrder->getPaidstatus() == 0)){
            $html = '<button type="button" class="button mst_pay_seller" total_amount="'.$dataSaleOrder->getActualparterprocost().'" product_id="'.$dataSaleOrder->getProdid().'" table_id="'.$saleOrderId.'" title="'.__('Pay Item').'"><span><span><span>'.__('Pay Item').'</span></span></span></button>';
        }elseif(($dataSaleOrder->getOrderStatus() != 'complete') && ($dataSaleOrder->getPaidstatus() == 0)){
            $html = __('Order Pending');
        }else{
            if(($dataSaleOrder->getOrderStatus() == 'complete') && ($dataSaleOrder->getPaidstatus() == 1)){
                $html = __('Already Paid');
            }
        }*/
        $html = '<button type="button" class="button view_transaction" tran_id="'.$row->getId().'" onclick="Magebay.viewTransaction('.$row->getId().')" title="'.__('View Transaction').'"><span><span><span>'.__('View Transaction').'</span></span></span></button>';
        return $html;
    }    
}