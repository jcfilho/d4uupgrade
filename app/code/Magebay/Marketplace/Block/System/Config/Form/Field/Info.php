<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebay\Marketplace\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Backend system config datetime field renderer
 */
class Info extends \Magento\Config\Block\System\Config\Form\Field
{

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @codeCoverageIgnore
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = base64_decode('PGRpdiBzdHlsZT0iY2xlYXI6IGJvdGg7IiA+PGEgaHJlZj0iaHR0cDovL21hZ2ViYXkuY29tLyIgdGFyZ2V0PSJfYmxhbmsiID48aW1nIHdpZHRoPSIxMDAlIiBzcmM9Imh0dHA6Ly9tYWdlYmF5LmNvbS9pbnRyby9pbnRyb19tYWdlYmF5LmpwZyIgYWx0PSIiIC8+PC9hPjwvZGl2Pg==');
        
        return $html;
    }
}