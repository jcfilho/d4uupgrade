<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Product;

class Builder {

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;   

    /**
     * @var StoreFactory
     */
    protected $storeFactory;
	
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;	
	
    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Registry $registry 
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Registry $registry,
		\Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->productFactory = $productFactory;
        $this->registry = $registry;        
        $this->_objectManager = $objectManager;        
    }
	
    /**
     * Build product based on user request
     *
     * @param RequestInterface $request
     * @return \Magento\Catalog\Model\Product
     */
    public function build(\Magento\Framework\App\RequestInterface $request)
    {
        $productId = (int)$request->getParam('id');
        $product = $this->productFactory->create();		
        $product->setStoreId($request->getParam('store', 0));

        $typeId = $request->getParam('type');
        if (!$productId && $typeId) {
            $product->setTypeId($typeId);
        }
        $product->setData('_edit_mode', true);
        if ($productId) {
            try {
                $product->load($productId);
            } catch (\Exception $e) {
                $product->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE);
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            }
        }

        $setId = (int)$request->getParam('set');
        if ($setId) {
            $product->setAttributeSetId($setId);
        }

        $this->registry->register('product', $product);
        $this->registry->register('current_product', $product);
        return $product;	
	}	
}