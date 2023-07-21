<?php
/**
 * Created by PhpStorm.
 * User: jose
 * Date: 2/7/19
 * Time: 11:07 AM
 */

namespace Daytours\Catalog\Plugin\Model\Product;


class Url
{
    public function afterGetUrl(\Magento\Catalog\Model\Product\Url $subject, $result,$product, $params = [])
    {
        $lastCharracter = substr($result,-1 );
        if( $lastCharracter != '/' ){
            $result = $result.'/';
        }
        return $result;
    }
}