<?php

namespace Magebay\Marketplace\Block\Product\Widget\Grid\Filter;

class SkipList extends \Magebay\Marketplace\Block\Product\Widget\Grid\Filter\AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    public function getCondition()
    {
        return ['nin' => $this->getValue() ?: [0]];
    }
}
