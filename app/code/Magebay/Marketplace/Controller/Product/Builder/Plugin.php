<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
 namespace Magebay\Marketplace\Controller\Product\Builder;
 
 class Plugin {

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurableType;

    /**
     * @param Type\Configurable $configurableType
     */
    public function __construct(\Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType)
    {
        $this->configurableType = $configurableType;
    }
	
    /**
     * @param \Magebay\Marketplace\Controller\Product\Builder $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function aroundBuild(
        \Magebay\Marketplace\Controller\Product\Builder $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $product = $proceed($request);

        if ($request->has('attributes')) {
            $attributes = $request->getParam('attributes');
            if (!empty($attributes)) {
                $product->setTypeId(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);
                $this->configurableType->setUsedProductAttributes($product, $attributes);
            } else {
                $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
            }
        }
		
		return $product;
	}	
 }