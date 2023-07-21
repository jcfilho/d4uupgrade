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
class TransactionsGridPaymentsStatus extends AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row->getPaidStatus() == 1) $cell = '<span class="status_yellow">Pending</span>';
        if ($row->getPaidStatus() == 2) $cell = '<span class="status_green">Completed</span>';
        if ($row->getPaidStatus() == 3) $cell = '<span class="status_gray">Canceled</span>';
        $cell .='<style>
                    .status_green, .status_yellow, .status_gray {
                        border-radius: 15px;
                        color: #ffffff;
                        display: block;
                        font-weight: bold;
                        padding: 2px 5px;
                        text-align: center;
                        width: 100px;
                    }
                    .status_gray {
                        background-color: #999999;
                    }
                    .status_green {
                        background-color: #468847;
                    }
                    .status_yellow{
                        background-color: #f89406;
                    }
                </style>';
        return $cell;
    }
}