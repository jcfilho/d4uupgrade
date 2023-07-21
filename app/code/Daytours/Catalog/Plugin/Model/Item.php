<?php

namespace Daytours\Catalog\Plugin\Model;

class Item
{

    public function aroundProcessBuyRequest(\Magento\Wishlist\Model\Item $product, \Closure $proceed)
    {
        $product = $proceed();
        $productTypeId = $product->getTypeId();
        if( $productTypeId == \Magebay\Bookingsystem\Model\Product\Type\Booking::TYPE_CODE ){

        }

        return $product ;
    }

}