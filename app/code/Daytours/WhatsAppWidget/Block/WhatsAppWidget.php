<?php

namespace Daytours\WhatsAppWidget\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;

class WhatsAppWidget extends \Magento\Framework\View\Element\Template
{

    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
    }


    public function isEnabled(){
        return $this->scopeConfig->getValue('wappwidget/general/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getPhoneNumber(){
        return $this->scopeConfig->getValue('wappwidget/general/phone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


}
