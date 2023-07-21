/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'paypal_buttons',
                component: 'Daytours_PayPalButtons/js/view/payment/method-renderer/paypal_buttons'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
