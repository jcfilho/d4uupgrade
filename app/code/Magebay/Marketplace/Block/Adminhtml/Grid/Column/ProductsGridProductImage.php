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
use Magebay\Marketplace\Model\Image as ImageModel;
use Magento\Catalog\Helper\Image as ImageProduct;

class ProductsGridProductImage extends AbstractRenderer
{
    protected $_imageModel;
    protected $_imageProduct;
    protected $_product;
    protected $_objectmanager;

    public function __construct(
		ImageModel $ImageModel,
        ImageProduct $ImageProduct,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
		$this->_imageModel = $ImageModel;
        $this->_imageProduct = $ImageProduct;
        $this->_product = $product;
        $this->_objectmanager = $objectmanager;
    }
    
    public function render(\Magento\Framework\DataObject $row)
    {
        $product = $this->_product->load($row->getProductId());      
		$image = 'category_page_grid';
		$productImage = $this->_imageProduct->init($product, 'product_listing_thumbnail_preview')->getUrl();
    	$strImage = '<img width="50" height="50" src="'.$productImage.'" />';
    	return $strImage;
    }
    
    function getMediaBaseUrl() {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $om->get('Magento\Store\Model\StoreManagerInterface');
        $currentStore = $storeManager->getStore();
        return $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
        
    function getBaseUrl() {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $om->get('Magento\Store\Model\StoreManagerInterface');
        $currentStore = $storeManager->getStore();
        return $currentStore->getBaseUrl();
    }    
}