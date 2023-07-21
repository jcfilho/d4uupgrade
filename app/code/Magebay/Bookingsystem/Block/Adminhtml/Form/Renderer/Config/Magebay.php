<?php

namespace Magebay\Bookingsystem\Block\Adminhtml\Form\Renderer\Config;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
class Magebay extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = base64_decode('PGRpdiBzdHlsZT0iY2xlYXI6IGJvdGg7IiA+PGEgaHJlZj0iaHR0cDovL21hZ2ViYXkuY29tLyIgdGFyZ2V0PSJfYmxhbmsiID48aW1nIHdpZHRoPSIxMDAlIiBzcmM9Imh0dHA6Ly9tYWdlYmF5LmNvbS9pbnRyby9pbnRyb19tYWdlYmF5LmpwZyIgYWx0PSIiIC8+PC9hPjwvZGl2Pg==');
        return $html;       
    }
}