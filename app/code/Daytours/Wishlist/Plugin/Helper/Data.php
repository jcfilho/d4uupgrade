<?php
/**
 * Created by PhpStorm.
 * User: jose
 * Date: 2/25/19
 * Time: 10:10 AM
 */

namespace Daytours\Wishlist\Plugin\Helper;


class Data
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    private $postDataHelper;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Framework\UrlInterface $url
    )
    {
        $this->storeManager = $storeManager;
        $this->postDataHelper = $postDataHelper;
        $this->url = $url;
    }


    /**
     * @param \Magento\Wishlist\Helper\Data $subject
     * @param \Closure $proceed
     * @param $item
     * @param array $params
     * @return mixed
     */
    public function aroundGetAddParams(\Magento\Wishlist\Helper\Data $subject, \Closure $proceed,$item, array $params = [])
    {
        $productId = null;
        if ($item instanceof \Magento\Catalog\Model\Product) {
            $productId = $item->getEntityId();
        }
        if ($item instanceof \Magento\Wishlist\Model\Item) {
            $productId = $item->getProductId();
        }

        $url = $this->_getUrlStore($item)->getUrl('wishlist/index/add');
        if ($productId) {
            $params['product'] = $productId;
            $params['redirectFromWishlist'] = $this->url->getCurrentUrl();
        }

        return $this->postDataHelper->getPostData($url, $params);
    }

    /**
     * @param $item
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getUrlStore($item)
    {
        $storeId = null;
        $product = null;
        if ($item instanceof \Magento\Wishlist\Model\Item) {
            $product = $item->getProduct();
        } elseif ($item instanceof \Magento\Catalog\Model\Product) {
            $product = $item;
        }
        if ($product) {
            if ($product->isVisibleInSiteVisibility()) {
                $storeId = $product->getStoreId();
            } else {
                if ($product->hasUrlDataObject()) {
                    $storeId = $product->getUrlDataObject()->getStoreId();
                }
            }
        }
        return $this->storeManager->getStore($storeId);
    }
}