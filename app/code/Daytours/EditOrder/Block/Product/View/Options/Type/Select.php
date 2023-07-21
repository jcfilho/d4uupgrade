<?php

namespace Daytours\EditOrder\Block\Product\View\Options\Type;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Block\Product\View\Options\Type\Select\CheckableFactory;
use Magento\Catalog\Block\Product\View\Options\Type\Select\MultipleFactory;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Value;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\View\Element\Template\Context;

class Select extends \Magento\Catalog\Block\Product\View\Options\Type\Select
{
    /**
     * @var \Daytours\EditOrder\Helper\Option
     */
    protected $optionHelper;


    public function __construct(
        \Daytours\EditOrder\Helper\Option $optionHelper,
        Context $context,
        Data $pricingHelper,
        CatalogHelper $catalogData,
        array $data = [],
        CheckableFactory $checkableFactory = null,
        MultipleFactory $multipleFactory = null
    )
    {
        parent::__construct($context, $pricingHelper, $catalogData, $data, $checkableFactory, $multipleFactory);
        $this->optionHelper = $optionHelper;
    }

    public function isEditable()
    {
        $_option = $this->getOption();
        foreach ($_option->getValues() as $_value) {
            if ($this->optionHelper->isCustomOptionEditable($_value)) {
                return true;
            }
        }

        return false;
    }

    public function toHtml()
    {
        if ($this->isEditable()) {
            return parent::toHtml();
        } else {
            return '';
        }
    }

    public function getValuesHtml(): string
    {
        $_option = $this->getOption();
        $configValue = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $_option->getId());
        $store = $this->getProduct()->getStore();
        $productId = $this->getProduct()->getId();
        $itemId = $this->getCurrentItem()->getId();

        $this->setSkipJsReloadPrice(1);
        // Remove inline prototype onclick and onchange events

        if ($_option->getType() == ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN ||
            $_option->getType() == ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE
        ) {
            $require = $_option->getIsRequire() ? ' required' : '';
            $extraParams = '';
            $select = $this->getLayout()->createBlock(
                \Magento\Framework\View\Element\Html\Select::class
            )->setData(
                [
                    'id' => 'select_' . $itemId . '_' . $productId . '_' . $_option->getId(),
                    'class' => $require . ' product-custom-option admin__control-select'
                ]
            );
            if ($_option->getType() == ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN) {
                $select->setName('options[' . $itemId . '][' . $productId . '][' . $_option->getid() . ']')->addOption('', __('-- Please Select --'));
            } else {
                $select->setName('options[' . $itemId . '][' . $productId . '][' . $_option->getid() . '][]');
                $select->setClass('multiselect admin__control-multiselect' . $require . ' product-custom-option');
            }
            foreach ($_option->getValues() as $_value) {
                $priceStr = $this->_formatPrice(
                    [
                        'is_percent' => $_value->getPriceType() == 'percent',
                        'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent'),
                    ],
                    false
                );
                $params = ['price' => $this->pricingHelper->currencyByStore($_value->getPrice(true), $store, false)];
                if ($this->isSelected($_option, $_value)) {
                    $params['selected'] = 'selected';
                }
                $select->addOption(
                    $_value->getOptionTypeId(),
                    $_value->getTitle() . ' ' . strip_tags($priceStr) . '',
                    $params
                );
            }
            if ($_option->getType() == ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE) {
                $extraParams = ' multiple="multiple"';
            }
            if (!$this->getSkipJsReloadPrice()) {
                $extraParams .= ' onchange="opConfig.reloadPrice()"';
            }
            $extraParams .= ' data-selector="' . $select->getName() . '"';
            $select->setExtraParams($extraParams);

            if ($configValue) {
                $select->setValue($configValue);
            }

            return $select->getHtml();
        }

        if ($_option->getType() == ProductCustomOptionInterface::OPTION_TYPE_RADIO ||
            $_option->getType() == ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX
        ) {
            $selectHtml = '<div class="options-list nested" id="options-' . $itemId . '-' . $productId . '-' . $_option->getId() . '-list">';
            $require = $_option->getIsRequire() ? ' required' : '';
            $arraySign = '';
            switch ($_option->getType()) {
                case ProductCustomOptionInterface::OPTION_TYPE_RADIO:
                    $type = 'radio';
                    $class = 'radio admin__control-radio';
                    if (!$_option->getIsRequire()) {
                        $selectHtml .= '<div class="field choice admin__field admin__field-option">' .
                            '<input type="radio" id="options_' . $itemId . '_' . $productId . '_' .
                            $_option->getId() .
                            '" class="' .
                            $class .
                            ' product-custom-option" name="options[' . $itemId . '][' . $productId . '][' .
                            $_option->getId() .
                            ']"' .
                            ' data-selector="options[' . $itemId . '][' . $productId . '][' . $_option->getId() . ']"' .
                            ($this->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"') .
                            ' value="" checked="checked" /><label class="label admin__field-label" for="options_' . $itemId . '_' . $productId . '_' .
                            $_option->getId() .
                            '"><span>' .
                            __('None') . '</span></label></div>';
                    }
                    break;
                case ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX:
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
                    ]
                );

                $htmlValue = $_value->getOptionTypeId();
                $checked = $this->isSelected($_option, $_value) ? 'checked' : '';
                $dataSelector = 'options[' . $itemId . '][' . $productId . '][' . $_option->getId() . ']';
                if ($arraySign) {
                    $dataSelector .= '[' . $htmlValue . ']';
                }

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
                    ($this->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"') .
                    ' name="options[' . $itemId . '][' . $productId . '][' .
                    $_option->getId() .
                    ']' .
                    $arraySign .
                    '" id="options_' . $itemId . '_' . $productId . '_' .
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
                    '<label class="label admin__field-label" for="options_' . $itemId . '_' . $productId . '_' .
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
    }

    /**
     * checks if the options is selected
     *
     * @param Option $option
     * @param Value $value
     * @return string
     */
    public function isSelected($option, $value)
    {
        $selected = false;
        $options = $this->getCurrentItem()->getProductOptions();
        if (
            isset($options['info_buyRequest'])
            && isset($options['info_buyRequest']['options'])
            && isset($options['info_buyRequest']['options'][$option->getId()])
        ) {
            if (is_array($options['info_buyRequest']['options'][$option->getId()])) {
                $selected = in_array($value->getId(), $options['info_buyRequest']['options'][$option->getId()]);
            } else {
                $selected = $value->getId() == $options['info_buyRequest']['options'][$option->getId()];
            }
        }

        return $selected;
    }
}
