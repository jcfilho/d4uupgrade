<?php

namespace Daytours\Catalog\Plugin\Block\Product\View;

class Options
{


    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $pricingHelper;
    /**
     * @var \Magento\Catalog\Helper\Data
     */
    private $_catalogData;
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;
    /**
     * System event manager
     *
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder
    )
    {

        $this->pricingHelper = $pricingHelper;
        $this->_catalogData = $catalogData;
        $this->jsonEncoder = $jsonEncoder;
        $this->_eventManager = $context->getEventManager();
    }

    public function aroundGetJsonConfig(\Magento\Catalog\Block\Product\View\Options $subject, \Closure $proceed)
    {
        $config = [];
        foreach ($subject->getOptions() as $option) {
            /* @var $option \Magento\Catalog\Model\Product\Option */
            if ($option->hasValues()) {
                $tmpPriceValues = [];
                foreach ($option->getValues() as $valueId => $value) {
                    $tmpPriceValues[$valueId] = $this->_getPriceConfiguration($value,$option->getIsMultiplier(),$option->getIsChild());
                }
                $priceValue = $tmpPriceValues;
            } else {
                $priceValue = $this->_getPriceConfiguration($option,$option->getIsMultiplier(),$option->getIsChild());
            }
            $config[$option->getId()] = $priceValue;
        }

        $configObj = new \Magento\Framework\DataObject(
            [
                'config' => $config,
            ]
        );

        //pass the return array encapsulated in an object for the other modules to be able to alter it eg: weee
        $this->_eventManager->dispatch('catalog_product_option_price_configuration_after', ['configObj' => $configObj]);

        $config=$configObj->getConfig();

        return $this->jsonEncoder->encode($config);
    }

    /**
     * Get price configuration
     *
     * @param \Magento\Catalog\Model\Product\Option\Value|\Magento\Catalog\Model\Product\Option $option
     * @return array
     */
    protected function _getPriceConfiguration($option,$isMultiplier,$isChild)
    {
        $optionPrice = $this->pricingHelper->currency($option->getPrice(true), false, false);
        $optionPriceChild = $this->pricingHelper->currency($option->getChildPrice(), false, false);
        $data = [
            'prices' => [
                'oldPrice' => [
                    'amount' => $this->pricingHelper->currency($option->getRegularPrice(), false, false),
                    'adjustments' => [],
                ],
                'basePrice' => [
                    'amount' => $this->_catalogData->getTaxPrice(
                        $option->getProduct(),
                        $optionPrice,
                        false,
                        null,
                        null,
                        null,
                        null,
                        null,
                        false
                    ),
                ],
                'finalPrice' => [
                    'amount' => $this->_catalogData->getTaxPrice(
                        $option->getProduct(),
                        $optionPrice,
                        true,
                        null,
                        null,
                        null,
                        null,
                        null,
                        false
                    ),
                ],
                'childPrice' => [
                    'amount' => $this->_catalogData->getTaxPrice(
                        $option->getProduct(),
                        $optionPriceChild,
                        true,
                        null,
                        null,
                        null,
                        null,
                        null,
                        false
                    ),
                ],
                'isMultiplier' => [
                    'amount' => 0,
                    'result' => $isMultiplier
                ],
                'isChild' => [
                    'amount' => 0,
                    'result' => $isChild
                ]

            ],
            'type' => $option->getPriceType(),
            'name' => $option->getTitle()
        ];
        return $data;
    }


}