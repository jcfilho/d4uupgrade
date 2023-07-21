<?php

namespace Daytours\Wordpress\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\ScopeInterface;
use \FishPig\WordPress\Block\Context as WPContext;

class Data extends AbstractHelper{

    const KEY_ID_MENU = 'wordpress/multisite/menu_id';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    private $_registry;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        WPContext $wpContext
    )
    {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->_registry = $wpContext->getRegistry();
    }

    public function getMenuId(){
        return $this->scopeConfig->getValue(self::KEY_ID_MENU,ScopeInterface::SCOPE_STORE);
    }

    public function getCurrentTerm(){
        /*Used on category list*/
        return $this->_registry->registry('wordpress_term')->getData();
    }

    public function getCurrentUser(){
        /*Used on list by users*/
        return $this->_registry->registry('wordpress_user')->getData();
    }

}