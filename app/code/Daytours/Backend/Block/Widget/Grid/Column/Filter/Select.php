<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Daytours\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Select grid column filter
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Select extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    public function __construct(\Magento\Backend\Block\Context $context, \Magento\Framework\DB\Helper $resourceHelper, array $data = [])
    {
        parent::__construct($context, $resourceHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getHtml()
    {
        $html = '<select name="' . $this->_getHtmlName() . '" id="' . $this->_getHtmlId() . '"' . $this->getUiId(
            'filter',
            $this->_getHtmlName()
        ) . 'class="no-changes admin__control-select">';
        $value = $this->getValue();
        foreach ($this->_getOptions() as $option) {
            if( isset($option['value']) ){
                if (is_array($option['value'])) {
                    $html .= '<optgroup label="' . $this->escapeHtml($option['label']) . '">';
                    foreach ($option['value'] as $subOption) {
                        $html .= $this->_renderOption($subOption, $value);
                    }
                    $html .= '</optgroup>';
                } else {
                    $html .= $this->_renderOption($option, $value);
                }
            }
        }
        $html .= '</select>';
        return $html;
    }

}
