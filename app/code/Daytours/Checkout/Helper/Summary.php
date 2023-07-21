<?php
/**
 * Created by PhpStorm.
 * User: jose
 * Date: 12/18/18
 * Time: 11:58 AM
 */

namespace Daytours\Checkout\Helper;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\App\Helper\Context;

class Summary extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * Summary constructor.
     * @param PriceCurrencyInterface $priceCurrency
     * @param Context $context
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        Context $context
    )
    {
        parent::__construct($context);
        $this->priceCurrency = $priceCurrency;
    }

    public function formatPriceSummary($price,$symbol){
        $price = $this->priceCurrency->format((float)$price, false);
        //number_format((float)$price, 2, '.', '');
        return $price;
    }

    public function formatPriceSummaryWithoutSymbol($price){
        return number_format((float)$price, 2, '.', '');
    }
}