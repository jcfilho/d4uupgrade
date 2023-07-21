<?php
/**
 * Created by PhpStorm.
 * User: filho
 * Date: 5/10/18
 * Time: 12:04 AM
 */

namespace Daytours\Checkout\Plugin\Block\Checkout;



class LayoutProcessor
{

    public function aroundProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $subject, \Closure $proceed,$jsLayout)
    {
        $jsLayoutResult = $proceed($jsLayout);

//        $jsLayoutResult['components']['checkout']['children']['steps']['children']
//        ['billing-step']['children']['payment']['children']
//        ['payments-list']['children']['checkmo-form']['children']
//        ['form-fields']['children']['postcode']['sortOrder'] = 71;

//        $jsLayoutResult['components']['checkout']['children']['steps']['children']
//        ['billing-step']['children']['payment']['children']
//        ['payments-list']['children']['checkmo-form']['children']
//        ['form-fields']['children']['country_id']['sortOrder'] = 0;

        $jsLayoutResult['components']['checkout']['children']['steps']['children']
        ['billing-step']['children']['payment']['children']
        ['afterMethods']['children']['billing-address-form']
        ['children']['form-fields']['children']['country_id']
        ['tooltip']['description'] = __('Methods of payments may vary  depending your country.');

        $jsLayoutResult['components']['checkout']['children']['steps']['children']
        ['billing-step']['children']['payment']['children']
        ['afterMethods']['children']['billing-address-form']
        ['children']['form-fields']['children']['country_id']['sortOrder'] = 80;

        $jsLayoutResult['components']['checkout']['children']['steps']['children']
        ['billing-step']['children']['payment']['children']
        ['afterMethods']['children']['billing-address-form']
        ['children']['form-fields']['children']['region_id']['sortOrder'] = 81;

        $jsLayoutResult['components']['checkout']['children']['steps']['children']
        ['billing-step']['children']['payment']['children']
        ['afterMethods']['children']['billing-address-form']
        ['children']['form-fields']['children']['city']['sortOrder'] = 82;

        return $jsLayoutResult;
    }

}