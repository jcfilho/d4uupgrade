<?php

namespace Daytours\EditOrder\Block\Product\View\Options\Type;

class Text extends \Magento\Catalog\Block\Product\View\Options\Type\Text
{
    protected $optionHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        \Daytours\EditOrder\Helper\Option $optionHelper,
        array $data = []
    )
    {
        $this->optionHelper = $optionHelper;

        parent::__construct($context, $pricingHelper, $catalogData, $data);
    }

    public function isEditable()
    {
        return $this->optionHelper->isCustomOptionEditable($this->getOption());
    }

    public function toHtml()
    {
        if ($this->isEditable()) {
            return parent::toHtml();
        } else {
            return '';
        }
    }

    /**
     * Returns default value to show in text input
     *
     * @return string
     */
    public function getDefaultValue()
    {
        $value = '';
        $options = $this->getCurrentItem()->getProductOptions();
        $option = $this->getOption();
        if (
            isset($options['info_buyRequest'])
            && isset($options['info_buyRequest']['options'])
            && isset($options['info_buyRequest']['options'][$option->getId()])
        ) {
            $value = $options['info_buyRequest']['options'][$option->getId()];
        }

        return $value;
    }
}
