<?php

namespace Daytours\RegularServices\Model\Product\Type\Booking;

use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

class Price extends \Magento\Catalog\Model\Product\Type\Price
{

    /**
     * @var PriceHelper
     */
    private $bkPriceHelper;

    public function __construct(
        \Magento\CatalogRule\Model\ResourceModel\RuleFactory $ruleFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        PriceCurrencyInterface $priceCurrency,
        GroupManagementInterface $groupManagement,
        \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $tierPriceFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        ProductTierPriceExtensionFactory $tierPriceExtensionFactory = null,
        PriceHelper $bkPriceHelper
)
    {
        parent::__construct($ruleFactory, $storeManager, $localeDate, $customerSession, $eventManager, $priceCurrency, $groupManagement, $tierPriceFactory, $config, $tierPriceExtensionFactory);
        $this->bkPriceHelper = $bkPriceHelper;
    }


    /**
     * @param $qty
     * @param $product
     * @param $basePrice
     * @param $basePriceWithOutBookingPrice
     * @return float|int
     */
    public function getFinalBookingPrice($qty, $product, $basePrice,$basePriceWithOutBookingPrice)
    {
        if ($this->hasRegularService($product)) {
            $finalPrice = $this->_getFinalBookingServicePrice($qty, $product, $basePrice,$basePriceWithOutBookingPrice);
        } else {
            $finalPrice = $this->_getFinalBookingPrice($qty, $product, $basePrice,$basePriceWithOutBookingPrice);
        }

        return $finalPrice;
    }

    /**
     * @param $qty
     * @param $product
     * @param $basePrice
     * @param $basePriceWithOutBookingPrice
     * @return float|mixed
     */
    protected function _getFinalBookingPrice($qty, $product, $basePrice,$basePriceWithOutBookingPrice)
    {
        $finalPrice = $basePrice;
        $finalPrice = $this->_applyOptionsPrice($product, $qty, $basePrice);
        $finalPrice = max(0, $finalPrice);

        return $finalPrice;
    }

    /**
     * @param $qty
     * @param $product
     * @param $basePrice
     * @param $basePriceWithOutBookingPrice
     * @return float|int
     */
    protected function _getFinalBookingServicePrice($qty, $product, $basePrice,$basePriceWithOutBookingPrice)
    {
//        $serviceBasePrice = $qty * $basePrice;
        $serviceBasePrice = $basePrice;
        $servicePrice = 0;
        $regularOptionPrice = 0;
        $serviceChildPrices = array();
        $childQtys = array();
        $childPrices = 0;

        $optionIds = $product->getCustomOption('option_ids');
        if ($optionIds) {
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $product->getOptionById($optionId)) {
                    $confItemOption = $product->getCustomOption('option_' . $option->getId());

                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setConfigurationItemOption($confItemOption);
                    $optionValue = $confItemOption->getValue();
                    $value = $option->getValueById($optionValue);

                    if ($option->getIsChild()) {
                        $childQtys[] = $value->getTitle();
                    }

                    if ($option->getIsMultiplier()) {
                        $serviceChildPrices[] =  $value->getChildPrice();
                        $servicePrice += $group->getOptionPrice($optionValue, $basePriceWithOutBookingPrice) * $qty;
                    } else {
                        $regularOptionPrice += $group->getOptionPrice($optionValue, $basePriceWithOutBookingPrice);
                    }
                }
            }
        }

        foreach ($childQtys as $childQty) {
            foreach ($serviceChildPrices as $serviceChildPrice) {
                //$childPrices += $childQty * $serviceChildPrice;
                // if(!is_numeric($childQty)){
                //     $childQty = is_string($childQty) ? inval($childQty) : 1;
                // }

                $childPrices += $childQty * $serviceChildPrice;
                // if(is_numeric($childQty) && is_numeric($serviceChildPrice)){
                // }
                // else{
                //     $childQtyParsed = is_string($childQty) ? 
                //     intval($childQty)
                // }
            }
        }

        $finalPrice = $serviceBasePrice + $servicePrice + $regularOptionPrice + $childPrices;

//        return $finalPrice / $qty;
        return $finalPrice;
    }

    /**
     * Determine whether a product has a regular service
     *
     * @param Product $product
     * @return float
     */
    public function hasRegularService($product)
    {
        $optionIds = $product->getCustomOption('option_ids');
        if ($optionIds) {
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $product->getOptionById($optionId)) {
                    if ($option->getIsMultiplier()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

}