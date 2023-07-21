<?php
/**
 * @Author      : David
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Adminhtml\Sellers\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sellers_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Seller Information'));
    }
	
	protected function _beforeToHtml()
    {
        $moduleManager = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Framework\Module\Manager');	
        if($moduleManager->isEnabled('Magebay_PaypalAdaptive')){
            $this->addTab('payment', array(
                'label' => __('Payment Info'),
                'title' => __('Payment Info'),
                'content' => $this->getLayout()->createBlock('Magebay\Marketplace\Block\Adminhtml\Sellers\Edit\Tab\Payment')->toHtml(),
            ));
        }

        $this->addTab(
            'related_products_section',
            [
                'label' => __('Seller Products'),
                'url' => $this->getUrl('marketplace/sellers/relatedProducts', ['_current' => true]),
                'class' => 'ajax',
            ]
        );
        
        if($moduleManager->isEnabled('Magebay_SellerStorePickup')){
            $this->addTab(
                'seller_store_pickup',
                [
                    'label' => __('Seller Store Pickup'),
                    'url' => $this->getUrl('magebay/sellerstorepickup/seller', ['_current' => true]),
                    'class' => 'ajax',
                ]
            );
        }
        return parent::_beforeToHtml();
    }
}