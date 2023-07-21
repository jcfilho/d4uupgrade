<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Product;

abstract class Product extends \Magebay\Marketplace\Controller\Product\Action\Action
{
    /**
     * @var Product\Builder
     */
    protected $productBuilder;

    /**
     * @param Action\Context $context
     * @param Product\Builder $productBuilder
     */
    public function __construct(
        \Magebay\Marketplace\Controller\Product\Action\Action\Context $context,
        \Magebay\Marketplace\Controller\Product\Builder $productBuilder
    ) {
        $this->productBuilder = $productBuilder;
        parent::__construct($context);
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
		return true;
    }
}