
define([
    'jquery',
    'Magento_Customer/js/model/authentication-popup',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/lib/core/storage/local',
    'mage/url',
],function($, authenticationPopup, customerData,storage,urlbuild){
    'use strict';

    return function (original) {
        return function (config, element) {
            $(element).click(function (event) {
                var cart = customerData.get('cart'),
                    customer = customerData.get('customer');

                event.preventDefault();

                if (!customer().firstname && cart().isGuestCheckoutAllowed === false) {
                    $("body .popup-with-form").trigger('click');
                    /*Flag to know if redirect to checkout after submit*/
                    var linkUrlCheckout = urlbuild.build('checkout');
                    $("#currentUrl").val(linkUrlCheckout);

                    return false;
                }
                location.href = config.checkoutUrl;
            });

        };
    };

});