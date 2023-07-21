/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery/ui',
    'underscore'
], function ($,$ui,_) {
    'use strict';

    $.widget('daytours.addToWishlistBooking', {
        options: {
            // bundleInfo: 'div.control [name^=bundle_option]',
            // configurableInfo: '.super-attribute-select',
            // groupedInfo: '#super-product-table input',
            // downloadableInfo: '#downloadable-links-list input',
            customOptionsInfo: '.cls-to-add-to-wishlist',
            qtyInfo: '#booking-qty'
        },

        /** @inheritdoc */
        _create: function () {
            this._init();
        },

        /**
         * @private
         */
        _init: function () {
            var options = this.options,
                dataUpdateFunc = '_updateWishlistData',
                changeCustomOption = 'change ' + options.customOptionsInfo,
                changeQty = 'change ' + options.qtyInfo,
                events = {},
                key;

            events[changeCustomOption] = dataUpdateFunc;
            events[changeQty] = dataUpdateFunc;

            this._on(events);
            this._verifyToLoadOptionsUpdateWishlist();
        },

        /**
         * @param {jQuery.Event} event
         * @private
         */
        _updateWishlistData: function (event) {
            //console.log(event);
            var dataToAdd = {},
                isFileUploaded = false,
                self = this;

            self._clearData('interval_one');
            self._clearData('interval_two');

            $(event.handleObj.selector).each(function (index, element) {
                if ($(element).is('input[type=text]') ||
                    $(element).is('input[type=email]') ||
                    $(element).is('input[type=number]') ||
                    $(element).is('input[type=hidden]') ||
                    $(element).is('input[type=checkbox]:checked') ||
                    $(element).is('input[type=radio]:checked') ||
                    $(element).is('textarea') ||
                    $('#' + element.id + ' option:selected').length
                ) {
                    dataToAdd = $.extend({}, dataToAdd, self._getElementData(element));
                    return;
                }

            });
            this._updateAddToWishlistButton(dataToAdd);
            event.stopPropagation();
        },

        /**
         * @param {Object} dataToAdd
         * @private
         */
        _updateAddToWishlistButton: function (dataToAdd) {
            var self = this;

            $('[data-action="add-to-wishlist"]').each(function (index, element) {
                var params = $(element).data('post');

                if (!params) {
                    params = {
                        'data': {}
                    };
                }

                params.data = $.extend({}, params.data, dataToAdd, {
                    'qty': $(self.options.qtyInfo).val()
                });
                $(element).data('post', params);
            });
        },

        _clearData : function(value1){
            var newData = {};
            var  currrentData = $('[data-action="add-to-wishlist"]').data('post');
            _.each(currrentData.data,function (valueObj,key) {
                if( key.search(value1) == -1 ){
                    newData[key] = valueObj;
                }
            });
            currrentData.data = newData;
            $('[data-action="add-to-wishlist"]').data('post',currrentData);
        },

        _getElementData: function (element) {
            var data, elementName, elementValue;

            element = $(element);
            data = {};
            elementName = element.data('selector') ? element.data('selector') : element.attr('name');
            elementValue = element.val();

            if (element.is('select[multiple]') && elementValue !== null) {
                if (elementName.substr(elementName.length - 2) == '[]') { //eslint-disable-line eqeqeq
                    elementName = elementName.substring(0, elementName.length - 2);
                }
                $.each(elementValue, function (key, option) {
                    data[elementName + '[' + option + ']'] = option;
                });
            } else {
                if (elementValue) { //eslint-disable-line no-lonely-if
                    if (elementName.substr(elementName.length - 2) == '[]') { //eslint-disable-line eqeqeq, max-depth
                        elementName = elementName.substring(0, elementName.length - 2);

                        if (elementValue) { //eslint-disable-line max-depth
                            data[elementName + '[' + elementValue + ']'] = elementValue;
                        }
                    } else {
                        data[elementName] = elementValue;
                    }
                }
            }

            return data;
        },
        _verifyToLoadOptionsUpdateWishlist: function () {
            var self = this;
            setTimeout(function(){
                if( $(self.options.customOptionsInfo).length > 0){
                    // console.log('element exist');
                    // console.log($(self.options.customOptionsInfo).length);
                    //self._updateAddToWishlistButton({});
                    var qtyCurrent = $('[name="qty"]').val();
                    $('[name="qty"]').trigger('change',[qtyCurrent]);
                }
            },1000)
        }
    });

    return $.daytours.addToWishlistBooking;
});
