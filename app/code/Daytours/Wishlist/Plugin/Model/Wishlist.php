<?php
namespace Daytours\Wishlist\Plugin\Model;

use \Magento\Catalog\Api\ProductRepositoryInterface;
use \Magento\Wishlist\Model\Wishlist as ModelWishlist;


class Wishlist
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_wislistItemOption;

    public function __construct(
        \Magento\Wishlist\Model\Item\Option $wislistItemOption
)
    {
        $this->_wislistItemOption = $wislistItemOption;
    }

    public function aroundAddNewItem(ModelWishlist $subject,\Closure $proceed,\Magento\Catalog\Model\Product $product,$buyRequest = null,$forciblySetQty = false)
    {
        $result = $proceed($product,$buyRequest,$forciblySetQty);
        $wishlistItemId = $result->getWishlistItemId();
        //$wishlistId = $result->getWishlistId();
        $productId = $result->getProductId();

        $textOptionBooking = \Daytours\Wishlist\Helper\Data::OPTION_TEXT_TO_BOOKING;

        $itemsData = $buyRequest->getData();
        if( isset($itemsData[$textOptionBooking]) ){
            $items = $itemsData[$textOptionBooking];
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            foreach ($items as $key => $item) {
                $itemOption = $objectManager->create(\Magento\Wishlist\Model\Item\Option::class);
                $itemOption->setWishlistItemId($wishlistItemId);
                $itemOption->setProductId($productId);
                $itemOption->setCode($textOptionBooking . '_' . $key);
                if( is_array($item) ){
                    $itemOption->setValue(implode(',',$item));
                }else{
                    $itemOption->setValue($item);
                }

                $itemOption->save();
            }
            if( isset($itemOption) ){
                return $itemOption;
            }
        }

        return $result;

    }

}