<?php

namespace Daytours\ProductQuestion\Block;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;

class ProductQuestion extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Product
     */
    private $product;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        array $data
    ) {
        $this->registry = $registry;

        parent::__construct($context, $data);
    }


    /**
     * @return Product
     */
    private function getProduct()
    {
        if (is_null($this->product)) {
            $this->product = $this->registry->registry('product');

            if (!$this->product->getId()) {
                throw new LocalizedException(__('Failed to initialize product'));
            }
        }

        return $this->product;
    }

    public function getProductName()
    {
        return $this->getProduct()->getName();
    }
}
