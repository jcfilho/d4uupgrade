<?php
/**
 * Created by PhpStorm.
 * User: jose
 * Date: 1/15/19
 * Time: 10:05 AM
 */

namespace Daytours\Hreflang\Block;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\CategoryRepository;

class GenerateHrefMeta extends Template
{
    CONST SUFFIX_URL_PRODUCT = 'catalog/seo/product_url_suffix';
    CONST SUFFIX_URL_CATEGORY = 'catalog/seo/category_url_suffix';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;
/*https://greenitsolutions.at/magento-rel-alternate-hreflang/*/
    public function __construct(
        StoreManagerInterface $storeManager,
        Registry $registry,
        ScopeConfigInterface $scopeConfig,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function getAllStore(){
        return $this->storeManager->getStores();
    }

    public function generateMetaHreflang($code,$url){
        return '<link rel="alternate" hreflang="'.$code.'" href="'.$url.'">';
    }

    public function buildURLProduct($store){
        $currentProduct = $this->registry->registry('product');
        $storeUrl = $this->getUrlToStore($store);

        $product = $this->productRepository->getById($currentProduct->getId(), false, $store->getId());
        ///$productUrlKey = $product->getUrlKey();
        return $product->getUrlModel()->getUrl($product, []);

//        $productSuffix = $this->scopeConfig->getValue(self::SUFFIX_URL_PRODUCT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
//        $productUrl = $storeUrl . $productUrlKey . $productSuffix;
//        return $productUrl . $productUrlDefault;
    }

    public function buildURLCategory($store){
        $currentCategory = $this->registry->registry('current_category');
        $storeUrl = $this->getUrlToStore($store);

        $category = $this->categoryRepository->get($currentCategory->getId(), $store->getId());
        //$categoryUrl = $category->getUrl(); // always return url to the current store view, does not change the url store correctly
        $categoryUrlPath = $category->getUrlPath();

        $categorySuffix = $this->scopeConfig->getValue(self::SUFFIX_URL_PRODUCT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $categoryUrl = $storeUrl . $categoryUrlPath . $categorySuffix;
        return $categoryUrl;
    }

    public function getUrlToStore($store){
        return $this->storeManager->getStore($store->getId())->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB) . $store->getCode() . '/';
    }
    public function getUrlToCmsPage($store,$urlCmsPage){
        $storeUrl = $this->getUrlToStore($store);
        $storeUrl = substr($storeUrl, 0, -1);
        return $storeUrl . $urlCmsPage;
    }

    public function generateUrlsToHrefLangMetaTag(){
        $stores = $this->getAllStore();
        $product = $this->registry->registry('product');
        $category = $this->registry->registry('current_category');

        if( $product ){
            foreach ($stores as $store){
                echo $this->generateMetaHreflang($store->getCode(),$this->buildURLProduct($store));
            }
        }elseif ($category){
            foreach ($stores as $store){
                echo $this->generateMetaHreflang($store->getCode(),$this->buildURLCategory($store));
            }
        }elseif( $this->_request->getFullActionName() == 'cms_index_index' ){
            foreach ($stores as $store){
                echo $this->generateMetaHreflang($store->getCode(),$this->getUrlToStore($store));
            }
        }elseif( $this->_request->getFullActionName() == 'cms_page_view' ){
            foreach ($stores as $store){
                echo $this->generateMetaHreflang($store->getCode(),$this->getUrlToCmsPage($store,$this->_request->getPathinfo()));
            }
        }
    }
}
