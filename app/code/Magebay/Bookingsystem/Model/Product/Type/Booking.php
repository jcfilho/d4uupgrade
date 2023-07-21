<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebay\Bookingsystem\Model\Product\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
/**
 * Dream product type implementation
 */
class Booking extends \Magento\Catalog\Model\Product\Type\AbstractType
{
	 /**
     * Construct
     *
     * @param \Magento\Catalog\Model\Product\Option $catalogProductOption
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Psr\Log\LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
	protected $_scopeConfig;
	
	public function __construct(
        \Magento\Catalog\Model\Product\Option $catalogProductOption,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
		$this->_scopeConfig = $scopeConfig;
       parent::__construct($catalogProductOption,$eavConfig,$catalogProductType,$eventManager,$fileStorageDb,$filesystem,$coreRegistry,$logger,$productRepository);
    }
	 /**
     * Product type code
     */
	const TYPE_CODE = 'booking';
    /**
     * Delete data specific for Simple product type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
    }
	public function isVirtual($product)
    {
		$enableShipping = $this->_scopeConfig->getValue('bookingsystem/setting/enable_shipping',ScopeInterface::SCOPE_STORE); 
        if($enableShipping == 1)
		{
			return false;
		}
		else
		{
			return true;
		}
    }
}
