/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'sidebar',
    'mage/translate'
    ],
    function (Component, $, ko, _) {
        'use strict';

        return function (Component) {
            return Component.extend({
                shoppingCartUrl: window.checkout.shoppingCartUrl,
                maxItemsToDisplay: window.checkout.maxItemsToDisplay,
                cart: {},

                /**
                 * Returns array of cart items, limited by 'maxItemsToDisplay' setting
                 * @returns []
                 */
                getCartItems: function () {
                    var items = this.getCartParam('items') || [];

                    items = items.slice(parseInt(-this.maxItemsToDisplay, 10));

                    _.each(items,function(product,indexProduct){
                        var elementsRegularService = product.options;
                        _.each(elementsRegularService,function(item,indiceRS){
                            if( item.label === 'regular_services' ){
                                var elementsRS = item.value;
                                if(!_.isArray(elementsRS)){
                                    var jsonRS = $.parseJSON(elementsRS);
                                    items[indexProduct].options[indiceRS].value = jsonRS;
                                }
                            }
                        });
                    });

                    return items;
                }
            });
        }
});