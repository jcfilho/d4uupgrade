/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'ko',
    'underscore',
    'overlay-minicart',
    'sidebar',
    'mage/translate'
], function (Component, customerData, $, ko, _,overlayMinicart) {
    'use strict';

    var sidebarInitialized = false,
        addToCartCalls = 0,
        miniCart;

    miniCart = $('[data-block=\'minicart\']');

    /**
     * @return {Boolean}
     */
    function initSidebar() {

        if (miniCart.data('mageSidebar')) {
            miniCart.sidebar('update');
        }

        if (!$('[data-role=product-item]').length) {
            return false;
        }
        miniCart.trigger('contentUpdated');

        if (sidebarInitialized) {
            return false;
        }
        sidebarInitialized = true;
        miniCart.sidebar({
            'targetElement': 'div.block.block-minicart',
            'url': {
                'checkout': window.checkout.checkoutUrl,
                'update': window.checkout.updateItemQtyUrl,
                'remove': window.checkout.removeItemUrl,
                'loginUrl': window.checkout.customerLoginUrl,
                'isRedirectRequired': window.checkout.isRedirectRequired
            },
            'button': {
                'checkout': '#top-cart-btn-checkout',
                'remove': '#mini-cart a.action.delete',
                'close': '#btn-minicart-close'
            },
            'showcart': {
                'parent': 'span.counter',
                'qty': 'span.counter-number',
                'label': 'span.counter-label'
            },
            'minicart': {
                'list': '#mini-cart',
                'content': '#minicart-content-wrapper',
                'qty': 'div.items-total',
                'subtotal': 'div.subtotal span.price',
                'maxItemsVisible': window.checkout.minicartMaxItemsVisible
            },
            'item': {
                'qty': ':input.cart-item-qty',
                'button': ':button.update-cart-item'
            },
            'confirmMessage': $.mage.__('Are you sure you would like to remove this item from the shopping cart?')
        });

    }

    miniCart.on('dropdowndialogopen', function () {
        initSidebar();
        overlayMinicart.openOverlay();
    });

    return Component.extend({
        shoppingCartUrl: window.checkout.shoppingCartUrl,
        maxItemsToDisplay: window.checkout.maxItemsToDisplay,
        cart: {},

        /**
         * @override
         */
        initialize: function () {
            var self = this,
                cartData = customerData.get('cart');

            this.update(cartData());
            cartData.subscribe(function (updatedCart) {
                addToCartCalls--;
                this.isLoading(addToCartCalls > 0);
                sidebarInitialized = false;
                this.update(updatedCart);
                initSidebar();
            }, this);
            $('[data-block="minicart"]').on('contentLoading', function () {
                addToCartCalls++;
                self.isLoading(true);
                $('[data-block="minicart"]').on('contentUpdated', function () {
                    $('[data-block="minicart"]').find('[data-role="dropdownDialog"]').dropdownDialog("open");
                    overlayMinicart.openOverlay();
                    $('#product-addtocart-button').prop('disabled',true);
                    $('#product-addtocart-button').addClass('disabled');
                    $('#product-addtocart-button').attr('type','button');
                })

                // setTimeout(function() {
                //     $('[data-block="minicart"]').find('[data-role="dropdownDialog"]').dropdownDialog("close");
                // }, 4000);

            });

            if( typeof cartData()['website_id'] !== 'undefined' ){
                if (cartData()['website_id'] !== window.checkout.websiteId) {
                    customerData.reload(['cart'], false);
                }
            }

            overlayMinicart.clickOverlayMinicart();
            return this._super();
        },
        isLoading: ko.observable(false),
        initSidebar: initSidebar,

        /**
         * Close mini shopping cart.
         */
        closeMinicart: function () {
            var self = this;
            $('[data-block="minicart"]').find('[data-role="dropdownDialog"]').dropdownDialog('close');
            overlayMinicart.closeOverlay();
        },
        /**
         * @return {Boolean}
         */
        closeSidebar: function () {
            var self = this;
            var minicart = $('[data-block="minicart"]');

            minicart.on('click', '[data-action="close"]', function (event) {
                event.stopPropagation();
                minicart.find('[data-role="dropdownDialog"]').dropdownDialog('close');
                overlayMinicart.closeOverlay();
            });

            return true;
        },

        /**
         * @param {String} productType
         * @return {*|String}
         */
        getItemRenderer: function (productType) {
            return this.itemRenderer[productType] || 'defaultRenderer';
        },

        /**
         * Update mini shopping cart content.
         *
         * @param {Object} updatedCart
         * @returns void
         */
        update: function (updatedCart) {
            _.each(updatedCart, function (value, key) {
                if (!this.cart.hasOwnProperty(key)) {
                    this.cart[key] = ko.observable();
                }
                this.cart[key](value);
            }, this);
        },

        /**
         * Get cart param by name.
         * @param {String} name
         * @returns {*}
         */
        getCartParam: function (name) {
            if (!_.isUndefined(name)) {
                if (!this.cart.hasOwnProperty(name)) {
                    this.cart[name] = ko.observable();
                }
            }

            return this.cart[name]();
        },

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
        },

        /**
         * Returns count of cart line items
         * @returns {Number}
         */
        getCartLineItemsCount: function () {
            var items = this.getCartParam('items') || [];

            return parseInt(items.length, 10);
        }
    });
});
