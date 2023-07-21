<?php

namespace Daytours\Catalog\Plugin\Model;

class Product
{

    public function aroundProcessBuyRequest(\Magento\Catalog\Model\Product $product, \Closure $proceed,\Magento\Framework\DataObject $buyRequest)
    {
        $options = $proceed($buyRequest);
        $productTypeId = $product->getTypeId();
        if( $productTypeId == \Magebay\Bookingsystem\Model\Product\Type\Booking::TYPE_CODE ){
            $customOptions = $buyRequest->getOptionsbooking();
            if (is_array($customOptions)) {
                array_filter(
                    $customOptions,
                    function ($value) {
                        return $value !== '';
                    }
                );
                $options->setOptionsbooking($customOptions);
            }
        }

        return $options ;
    }

}