<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Adminhtml\Grid\Column;
use \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
class PaymentsGridPaymentsStatus extends AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row->getIsActive() || $row->getStatus()) {
            $cell = '<span class="grid-severity-notice"><span>Enabled</span></span>';
        } else {
            $cell = '<span class="grid-severity-critical"><span>Disabled</span></span>';
        }
        return $cell;
    }
}