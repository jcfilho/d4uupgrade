<?php
/**
 * Created by PhpStorm.
 * User: jose
 * Date: 2/11/19
 * Time: 9:53 AM
 */

namespace Daytours\Catalog\Plugin\Model;


class Category
{
    public function afterGetUrl(\Magento\Catalog\Model\Category $subject, $result)
    {
        $lastCharracter = substr($result,-1 );
        if( $lastCharracter != '/' ){
            $result = $result.'/';
        }
        return $result;
    }
}