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
class ProductsGridProductStatus extends AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row->getStatus() == 0) {
            $cell = '<span class="status_yellow">Pending</span>';
        }elseif ($row->getStatus() == 1) {
            $cell = '<span class="status_green">Approved</span>';
        }elseif ($row->getStatus() == 2) {
            $cell = '<span class="status_gray">Unapproved</span>';
        }else{
            $cell = '<span class="status_black"><span>Not Submitted</span>';
        }
        return $cell;
    }
}