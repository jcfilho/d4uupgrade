<?php

namespace Daytours\Catalog\Plugin\Block\Product\View\Options\Type;

use Magento\Catalog\Pricing\Price\CustomOptionPriceInterface;

class Select
{

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $pricingHelper;

    public function __construct(
        \Magento\Framework\Pricing\Helper\Data $pricingHelper
    )
    {
        $this->pricingHelper = $pricingHelper;
    }

    public function aroundGetValuesHtml(\Magento\Catalog\Block\Product\View\Options\Type\Select $subject, \Closure $proceed)
    {
        $selectDefault = $proceed();
        if ($subject instanceof \Daytours\EditOrder\Block\Product\View\Options\Type\Select) {
            return $selectDefault;
        }

        $_option = $subject->getOption();
        if ($_option->getType() == \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN){

            $configValue = $subject->getProduct()->getPreconfiguredValues()->getData('options/' . $_option->getId());
            $store = $subject->getProduct()->getStore();

            $require = $_option->getIsRequire() ? ' required' : '';
            $extraParams = '';

            $select = $subject->getLayout()->createBlock(
                \Magento\Framework\View\Element\Html\Select::class
            )->setData(
                [
                    'id' => 'select_' . $_option->getId(),
                    'class' => $require . ' product-custom-option admin__control-select isMultiplierSelect'.$_option->getIsMultiplier() . ' isChild' . $_option->getIsChild()
                ]
            );
            $select->setName('options[' . $_option->getid() . ']')->addOption('', __('-- Please Select --'));

            foreach ($_option->getValues() as $_value) {
                $priceStr = $this->_formatPrice(
                    [
                        'is_percent' => $_value->getPriceType() == 'percent',
                        'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent'),
                    ],
                    false,
                    $subject
                );
                $select->addOption(
                    $_value->getOptionTypeId(),
                    $_value->getTitle() . ' ' . strip_tags($priceStr) . '',
                    [
                        'price' => $this->pricingHelper->currencyByStore($_value->getPrice(true), $store, false),
                        'price-child' => $this->pricingHelper->currencyByStore($_value->getChildPrice(), $store, false),
                        'qty' => is_numeric($_value->getTitle()) ? $_value->getTitle() : "0"
                    ]
                );
            }
            if ($_option->getType() == \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE) {
                $extraParams = ' multiple="multiple"';
            }
            $extraParams .= 'ld="ld"';
//            if (!$subject->getSkipJsReloadPrice()) {
//                $extraParams .= ' onchange="opConfig.reloadPrice()"';
//            }
            $extraParams .= ' data-selector="' . $select->getName() . '"';
            $select->setExtraParams($extraParams);

            if ($configValue) {
                $select->setValue($configValue);
            }

            return $select->getHtml();
        }





        if ($_option->getType() == \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_RADIO ||
            $_option->getType() == \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX
        ) {
            $configValue = $subject->getProduct()->getPreconfiguredValues()->getData('options/' . $_option->getId());
            $store = $subject->getProduct()->getStore();

            $selectHtml = '<div class="options-list nested" id="options-' . $_option->getId() . '-list">';
            $require = $_option->getIsRequire() ? ' required' : '';
            $arraySign = '';

            switch ($_option->getType()) {
                case \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_RADIO:

                    $type = 'radio';
                    $class = 'radio admin__control-radio  isMultiplier'.$_option->getIsMultiplier() . ' isChild' . $_option->getIsChild();
                    if (!$_option->getIsRequire()) {
                        $selectHtml .= '<div class="field choice admin__field admin__field-option">' .
                            '<input type="radio" id="options_' .
                            $_option->getId() .
                            '" class="' .
                            $class .
                            ' product-custom-option" name="options[' .
                            $_option->getId() .
                            ']"' .
                            ' data-selector="options[' . $_option->getId() . ']"' .
                            ($subject->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"') .
                            ' value="" checked="checked" /><label class="label admin__field-label" for="options_' .
                            $_option->getId() .
                            '"><span>' .
                            __('None') . '</span></label></div>';
                    }
                    break;
                case \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX:
                    $type = 'checkbox';
                    $class = 'checkbox admin__control-checkbox';
                    $arraySign = '[]';
                    break;
            }
            $count = 1;
            foreach ($_option->getValues() as $_value) {
                $count++;

                $priceStr = $this->_formatPrice(
                    [
                        'is_percent' => $_value->getPriceType() == 'percent',
                        'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent'),
                    ],
                    false,
                    $subject
                );

                $htmlValue = $_value->getOptionTypeId();
                if ($arraySign) {
                    $checked = is_array($configValue) && in_array($htmlValue, $configValue) ? 'checked' : '';
                } else {
                    $checked = $configValue == $htmlValue ? 'checked' : '';
                }

                $dataSelector = 'options[' . $_option->getId() . ']';
                if ($arraySign) {
                    $dataSelector .= '[' . $htmlValue . ']';
                }

                $mPrice = $this->pricingHelper->currencyByStore($_value->getPrice(true), $store, false);
                $mPriceChild = $this->pricingHelper->currencyByStore($_value->getChildPrice(), $store, false);
                $mQty = $this->pricingHelper->currencyByStore($_value->getChildPrice(), $store, false);
                $multiplerAttr = ' price = ' . $mPrice . ' price-child=' . $mPriceChild . ' qty=' . $mQty . ' ';

                $selectHtml .= '<div class="field choice admin__field admin__field-option' .
                    $require .
                    '">' .
                    '<input type="' .
                    $type .
                    '" class="' .
                    $class .
                    ' ' .
                    $require .
                    ' product-custom-option"' .
                    $multiplerAttr.
                    ($subject->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"') .
                    ' name="options[' .
                    $_option->getId() .
                    ']' .
                    $arraySign .
                    '" id="options_' .
                    $_option->getId() .
                    '_' .
                    $count .
                    '" value="' .
                    $htmlValue .
                    '" ' .
                    $checked .
                    ' data-selector="' . $dataSelector . '"' .
                    ' price="' .
                    $this->pricingHelper->currencyByStore($_value->getPrice(true), $store, false) .
                    '" />' .
                    '<label class="label admin__field-label" for="options_' .
                    $_option->getId() .
                    '_' .
                    $count .
                    '"><span>' .
                    $_value->getTitle() .
                    '</span> ' .
                    $priceStr .
                    '</label>';
                $selectHtml .= '</div>';
            }
            $selectHtml .= '</div>';

            return $selectHtml;
        }














        return $selectDefault;
    }

    protected function _formatPrice($value, $flag = true,$subject)
    {

        /** @var $subject \Magento\Framework\Pricing\Helper\Data */

        if ($value['pricing_value'] == 0) {
            return '';
        }

        $sign = '+';
        if ($value['pricing_value'] < 0) {
            $sign = '-';
            $value['pricing_value'] = 0 - $value['pricing_value'];
        }

        $priceStr = $sign;

        $customOptionPrice = $subject->getProduct()->getPriceInfo()->getPrice('custom_option_price');
        $context = [CustomOptionPriceInterface::CONFIGURATION_OPTION_FLAG => true];
        $optionAmount = $customOptionPrice->getCustomAmount($value['pricing_value'], null, $context);
        $priceStr .= $subject->getLayout()->getBlock('product.price.render.default')->renderAmount(
            $optionAmount,
            $customOptionPrice,
            $subject->getProduct()
        );

        if ($flag) {
            $priceStr = '<span class="price-notice">' . $priceStr . '</span>';
        }

        return $priceStr;
    }

}
