<?php

namespace Magebay\Marketplace\Block\Product\Widget\Grid\Renderer\Checkboxes;

class Extended extends \Magebay\Marketplace\Block\Product\Widget\Grid\Renderer\Checkbox
{
    /**
     * Prepare data for renderer
     *
     * @return array
     */
    public function _getValues()
    {
        return $this->getColumn()->getValues();
    }
}
