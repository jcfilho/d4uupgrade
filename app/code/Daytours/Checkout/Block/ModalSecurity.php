<?php
/**
 * Created by PhpStorm.
 * User: filho
 * Date: 5/13/18
 * Time: 8:12 AM
 */

namespace Daytours\Checkout\Block;

use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Element\Template;

class ModalSecurity extends Template
{
    const BLOCK_ID = 'modal_security_checkout';
    /**
     * @var LayoutInterface
     */
    private $layout;

    public function __construct(Template\Context $context, array $data = [],LayoutInterface $layout)
    {
        parent::__construct($context, $data);
        $this->layout = $layout;
    }

    public function getContentModalSecurity(){
        $block = $this->_layout->createBlock('Magento\Cms\Block\Block')
            ->setBlockId(self::BLOCK_ID)->toHtml();
        return $block;
    }
}