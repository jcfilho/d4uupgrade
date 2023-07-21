<?php

namespace Daytours\Catalog\Block;

use Magento\Framework\View\Element\Template;

class SubTotales extends Template{

    /**
     * @var \Magento\Directory\Model\Currency
     */
    private $currency;

    public function __construct(
         Template\Context $context,
         array $data = [],
         \Magento\Directory\Model\Currency $currency
     )
     {
         parent::__construct($context, $data);
         $this->currency = $currency;
     }


    public function getStoreCurrencySymbol(){
        return $this->currency->getCurrencySymbol();
    }
}