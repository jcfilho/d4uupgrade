/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'underscore',
    'mage/template',
    'jquery/jquery-ui',
    'mage/translate',
    'transferRountripPriceUtils',
    'jquery-ui-modules/widget'
], function ($, utils, _, mageTemplate,ui,$tr,rountripUtils) {
    'use strict';

    var globalOptions = {
        productId: null,
        priceConfig: null,
        prices: {},
        priceTemplate: '<span class="price"><%- data.formatted %></span>'
    };

    $.widget('mage.priceBox', {
        options: globalOptions,
        qtyInfo: '#qty',

        /**
         * Widget initialisation.
         * Every time when option changed prices also can be changed. So
         * changed options.prices -> changed cached prices -> recalculation -> redraw price box
         */
        _init: function initPriceBox() {
            var box = this.element;

            this.cache.optionsChildCalcule = {};
            this.cache.optionsChildCalcule.pricesFound = [];
            this.cache.optionsChildCalcule.qtySelected = 0;
            this.cache.optionsChildCalcule.bookingPrice = 0;
            this.cache.optionsChildCalcule.bookingPriceOld = 0;
            this.cache.optionsChildCalcule.roundtripPrice = 0;
            this.cache.optionsChildCalcule.roundtripOldPrice = 0;
            this.cache.optionsChildCalcule.roundtripOldPriceApplied = false;

            box.trigger('updatePrice');
            this.cache.displayPrices = utils.deepClone(this.options.prices);
            this.cache.optionsChildCalcule.basePrice = this.options.prices.finalPrice.amount;
            this.cache.optionsChildCalcule.oldPrice = ( typeof this.options.prices.oldPrice !== 'undefined') ? this.options.prices.oldPrice.amount : 0;
            this.changeQty();
        },

        /**
         * Widget creating.
         */
        _create: function createPriceBox() {
            var box = this.element;

            this.cache = {};
            this._setDefaultsFromPriceConfig();
            this._setDefaultsFromDataSet();

            box.on('reloadPrice', this.reloadPrice.bind(this));
            box.on('updatePrice', this.onUpdatePrice.bind(this));
            $(this.qtyInfo).on('input', this.updateProductTierPrice.bind(this));
            box.trigger('price-box-initialized');
        },
        removeOldPrice: function(){
            $('[data-price-type="oldPrice"]').remove();
        },
        enableOrDisablePriceRountrip: function(oldPrice = 0,price = 0){
            var self = this;
            self.cache.optionsChildCalcule.roundtripPrice = price;
            self.cache.optionsChildCalcule.roundtripOldPrice = oldPrice;
            self.cache.optionsChildCalcule.roundtripOldPriceApplied = false;

            if( self.cache.optionsChildCalcule.bookingPrice == 0 ){
                if( oldPrice > 0 ){
                    self.createHtmlToOldPrice(0);
                }else{
                    self.removeOldPrice();
                }
                self.updatePrice({});
            }
        },
        enableOrDisablePriceGoing: function(oldPrice = 0,price = 0){
            var self = this;

            if( self.cache.optionsChildCalcule.bookingPrice == 0 ) {
                self.cache.optionsChildCalcule.roundtripPrice = price;
                self.cache.optionsChildCalcule.roundtripOldPrice = oldPrice;

                if (self.cache.optionsChildCalcule.oldPrice > 0 && self.cache.optionsChildCalcule.oldPrice != self.cache.optionsChildCalcule.basePrice) {
                    self.createHtmlToOldPrice(0);
                } else {
                    self.removeOldPrice();
                }

                self.updatePrice({});
            }
        },
        enableDisableButtonAddToCart: function(){
            var self = this, activeInactive = true;

            if(self.cache.optionsChildCalcule.bookingPrice > 0){
                activeInactive = false;
                if( rountripUtils.ifExistOptionRoundtrip() && !rountripUtils.ifCalendarTwohasDateSelected() ){
                    activeInactive = true
                }
            }else{
                activeInactive = true;
            }
            $('.book-tours-form .box-tocart .action.tocart').prop('disabled',activeInactive);
        },
        isMultiplierProduct: function(){
            return $('.isMultiplier1').length;
        },
        changeQty: function(){
            var self = this;
            $('[name="qty"]').on('change',function(){
                var qty = $(this).val();
                if( !$.isNumeric(qty) ){
                    qty = 1;
                }
                self.updatePrice({});
            });
        },
        changeBookingPrice: function (price,oldPrice = 0) {
            var self = this;
            this.cache.optionsChildCalcule.bookingPrice = price;
            this.cache.optionsChildCalcule.bookingPriceOld = oldPrice;
            self.updatePrice({});
        },
        calculateChildrenPrices: function(){
            var total = {
                    totalChildren : 0,
                    totalDefaultValues : 0
                },
                self = this;
            if( !_.isEmpty(self.getQty()) && self.getChildActive()){

                var multipliers = $('#product_addtocart_form .isMultiplier1:checked');
                var child       = $('#product_addtocart_form .isChild1');
                $.each(child,function(indexChild,elementChild){
                    if( $(elementChild).find('option:selected').val() != '0' && $(elementChild).find('option:selected').val() != ''){
                        var qtyChildren = $(':selected',$(elementChild)).attr('qty');
                        $.each(multipliers,function(index,item){
                            var valueLabel = $(item).next('label').find('span').html();
                            if( $(item).val() !== '' && valueLabel !== 'no'  && valueLabel !== 'No'){
                                var priceChild = $(item).attr('price-child');
                                total.totalChildren = total.totalChildren + ( priceChild * qtyChildren);
                            }
                        });
                    }
                });
            }
            return total;
        },
        calculateChildrenPricesForDropdown: function(){
            var total = 0,
                self = this;
            if( !_.isEmpty(self.getQty()) && self.getChildActive()){

                var multipliers = $('#product_addtocart_form .isMultiplierSelect1');
                var child       = $('#product_addtocart_form .isChild1');
                $.each(child,function(indexChild,elementChild){
                    if( $(elementChild).find('option:selected').val() != '0' && $(elementChild).find('option:selected').val() != ''){
                        var qtyChildren = $(':selected',$(elementChild)).attr('qty');
                        $.each(multipliers,function(index,item){
                            var valueLabel = $(item).next('label').find('span').html();
                            if( $(item).val() !== '' && valueLabel !== 'no'  && valueLabel !== 'No'){
                                var priceChild = $(item).find(':selected').attr('price-child');
                                total += ( priceChild * qtyChildren);
                            }
                        });
                    }
                });
            }
            return total;
        },
        calculateMultiplierPrices: function(){
            var total = {
                    totalMultiplier : 0,
                    totalDefaultValues : 0
                },
                self = this;
            if( !_.isEmpty(self.getQty())){
                var multipliers = $('#product_addtocart_form .isMultiplier1:checked');
                $.each( multipliers,function(index,element){
                    if( $(element).val() != '0' && $(element).val() != ''){
                        var price = $(element).attr('price');
                        total.totalMultiplier = total.totalMultiplier + (self.getQty() * price);
                        total.totalDefaultValues = total.totalDefaultValues + parseFloat(price);
                    }
                });
            }
            return total;
        },
        getQty : function () {
            return $('[name="qty"]').val();
        },
        getChildActive:function(){
            var child = $('select.isChild1 option:selected');
            var chilQty = child.attr('qty');
            var chilValue = child.val();
            if( chilValue != '' && chilValue != null ){
                if( chilQty !== undefined && chilQty != null && chilQty != '' ){
                    return true;
                }
                return false;
            }

            return false;
        },
        calculateBasePriceToDiscount: function(){
            var self = this;
            return self.cache.optionsChildCalcule.basePrice;
        },
        calculateOldPriceToDiscount: function(){
            var self = this;
            return self.cache.optionsChildCalcule.oldPrice;
        },
        calculatePriceFromMultiplierDropdown: function(){
            var self = this;
            var priceAdults = 0;
            var multiplierSelectSelector = $('.isMultiplierSelect1');

            $.each(multiplierSelectSelector,function(index,item){
                var selectedValue = $(item).val();
                if( selectedValue != '' && selectedValue != 'no' && selectedValue != 'No' ){
                    var optionPrice = $(item).find(":selected").attr('price');
                    var optionPriceChild = $(item).find(":selected").attr('price-child');
                    var qty = self.getQty();
                    priceAdults -= optionPrice;
                    priceAdults += (qty * optionPrice);
                }
            });

            return priceAdults;
        },
        /**
         * Get label multiplier by element
         */
        getLabelMultiplier: function(element){
            var elementMultiplier = $(element);
            return elementMultiplier.parents('.field').find('.label span').html();
        },
        buildNewElementSubTotal: function(label,price,qty,symbol){
            if( symbol == '=' ){
                return '<p><b> ' + label +  ' </b> <span>' + utils.formatPrice(price) + ' x ' + qty + '</span></p>';
            }else{
                return '<p><b> ' + label +  ' </b> <span>' + utils.formatPrice(price) + ' x ' + qty + '</span></p>';
            }
        },
        ifIsTransfer: function(){
            if( $('#option-going').length > 0 || $('#option-roundtrip').length > 0 ){
                return true;
            }
            return false;
        },
        /**
         * Calculate total from options multiplier
         */
        calculateSubTotales: function(){
            var hasOptions  = false;
            var multipliers = $('#product_addtocart_form .isMultiplier1:checked');
            var multipliersSelect = $('#product_addtocart_form .isMultiplierSelect1');
            var child       = $('#product_addtocart_form .isChild1');
            var self        = this;
            var containerSubTotal       = $('.sub-total-price-regular');
            var containerSubTotalsAll   = $('.sub-total-price-regular .sub-total-content');

            var childElements = [];

            containerSubTotal.removeClass('show').addClass('hide');
            containerSubTotalsAll.html('');
            var dataSerialize = [];

            /*Set adults value*/

            if( !self.ifIsTransfer() ){
                var qtyAdults = self.getQty();
                var labelAdults = $tr('Adults');
                var priceBaseBooking = '';
                priceBaseBooking = self.cache.optionsChildCalcule.bookingPrice / qtyAdults;

                var newElement = self.buildNewElementSubTotal(labelAdults,priceBaseBooking,qtyAdults);
                if(priceBaseBooking > 0){
                    containerSubTotalsAll.append(newElement);
                    dataSerialize.push(
                        {
                            label   : labelAdults,
                            price   : utils.formatPrice(priceBaseBooking),
                            qty     : qtyAdults
                        }
                    );
                }
            }


            /*------*/


            /*Set child value*/
            if(child.length){
                var optionSelected = child.find('option:selected');
                if( optionSelected.val() !== '' && optionSelected.text() !== 'no'  && optionSelected.text() !== 'No'){

                    var qtyChildren = $(':selected',child).attr('qty');
                    var priceToChildren = $(':selected',child).attr('price') / qtyChildren;
                    var labelChildren = self.getLabelMultiplier(child);
                    var childrenPrice = utils.formatPrice(priceToChildren);
                    if( priceToChildren > 0 ){
                        var elementChildren = self.buildNewElementSubTotal(labelChildren,priceToChildren,qtyChildren);
                        containerSubTotalsAll.append(elementChildren);
                        dataSerialize.push({
                            label   : labelChildren,
                            price   : childrenPrice,
                            qty     : qtyChildren
                        });
                    }
                }
            }


            $.each(multipliers,function(index,item){
                var label = self.getLabelMultiplier(item);
                var qty = self.getQty();
                //var optionSelected = $(item).find('option:selected');
                var valueLabel = $(item).next('label').find('span').html();
                if( $(item).val() !== '' && valueLabel !== 'no'  && valueLabel !== 'No'){
                    var price = $(item).attr('price');
                    if( price > 0 ){
                        var newElement = self.buildNewElementSubTotal(label,price,qty);
                        containerSubTotalsAll.append(newElement);
                        hasOptions = true;
                        childElements.push({
                            label : label,
                            price : $(item).attr('price-child')
                        });
                        dataSerialize.push({
                            label   : label,
                            price   : utils.formatPrice(price),
                            qty     : qty
                        });
                    }
                }
            });

            $.each(multipliersSelect,function(index,item){
                var label = self.getLabelMultiplier(item);
                var qty = self.getQty();
                //var optionSelected = $(item).find('option:selected');
                var valueLabel = $(item).next('label').find('span').html();
                if( $(item).val() !== '' && valueLabel !== 'no'  && valueLabel !== 'No'){
                    var price = $(item).find(':selected').attr('price');
                    if( price > 0 ){
                        var newElement = self.buildNewElementSubTotal(label,price,qty);
                        containerSubTotalsAll.append(newElement);
                        hasOptions = true;
                        childElements.push({
                            label : label,
                            price : $(item).find(':selected').attr('price-child')
                        });
                        dataSerialize.push({
                            label   : label,
                            price   : utils.formatPrice(price),
                            qty     : qty
                        });
                    }
                }
            });


            if( hasOptions ){
                if(child.length){
                    var optionSelected = child.find('option:selected');
                    if( optionSelected.val() !== '' && optionSelected.text() !== 'no'  && optionSelected.text() !== 'No'){

                        $.each(childElements,function(index,item){
                            var labelFinal = item.label + ' ' + $tr('child');
                            var qtyChild = optionSelected.attr('qty');
                            if( item.price > 0){
                                var newElement = self.buildNewElementSubTotal(labelFinal,item.price,qtyChild);
                                containerSubTotalsAll.append(newElement);
                                dataSerialize.push({
                                    label   : labelFinal,
                                    price   : utils.formatPrice(item.price),
                                    qty     : qtyChild
                                });
                            }
                        });

                    }
                }
            }

            if(dataSerialize.length > 0){
                containerSubTotal.removeClass('hide').addClass('show');
                $('body #extra-options-rs').val(JSON.stringify(dataSerialize));
            }

        },
        /**
         * Call on event updatePrice. Proxy to updatePrice method.
         * @param {Event} event
         * @param {Object} prices
         */
        onUpdatePrice: function onUpdatePrice(event, prices) {
            return this.updatePrice(prices);
        },

        /**
         * Updates price via new (or additional values).
         * It expects object like this:
         * -----
         *   "option-hash":
         *      "price-code":
         *         "amount": 999.99999,
         *         ...
         * -----
         * Empty option-hash object or empty price-code object treats as zero amount.
         * @param {Object} newPrices
         */
        updatePrice: function updatePrice(newPrices) {

            var self = this;

            var isMultiplier = false,
                elementWithEvent,
                optionSelected,
                optionSelectedValue = 0,
                optionText,
                pricesChild = [],
                qtyAdults = self.getQty();

            var prices = this.cache.displayPrices,
                additionalPrice = {},
                pricesCode = [],
                priceValue, origin, finalPrice;

            self.cache.optionsChildCalcule.pricesFound = [];
            //self.cache.multiplier = [];

            if (newPrices !== undefined && _.size(newPrices) > 0)  {
                if (newPrices.hasOwnProperty('elementSelectorDt')) {
                    elementWithEvent = $(newPrices.elementSelectorDt.element);
                    optionSelected = $(newPrices.elementSelectorDt.element).find('option:selected');
                    optionSelectedValue = $(optionSelected).attr('qty');
                    if( $.isNumeric(optionSelectedValue) && $(newPrices.elementSelectorDt.element).is('select')){
                        self.cache.optionsChildCalcule.qtySelected = optionSelectedValue;
                    }
                    optionText = $.trim(elementWithEvent.text());
                }
            }

            this.cache.additionalPriceObject = this.cache.additionalPriceObject || {};

            if (newPrices) {
                $.extend(this.cache.additionalPriceObject, newPrices);
            }

            if (!_.isEmpty(additionalPrice)) {
                pricesCode = _.keys(additionalPrice);
            } else if (!_.isEmpty(prices)) {
                pricesCode = _.keys(prices);
            }
            _.each(this.cache.additionalPriceObject, function (additional,index) {
                if (additional && !_.isEmpty(additional)) {
                    pricesCode = _.keys(additional);
                }
                var isMultiplier= false;
                var isChild = false;

                _.each(pricesCode, function (priceCode) {
                    if( priceCode == 'isMultiplier'){
                        if( typeof additional[priceCode] !== 'undefined'){
                            if( additional[priceCode]['result'] == '1' ){
                                isMultiplier = true;
                            }
                        }
                    }
                    if( priceCode == 'isChild'){
                        if( typeof additional[priceCode] !== 'undefined'){
                            if( additional[priceCode]['result'] == '1' ){
                                isChild = true;
                            }
                        }
                    }
                });

                _.each(pricesCode, function (priceCode) {
                    if( priceCode != 'isMultiplier' && priceCode != 'isChild' ){
                        priceValue = additional[priceCode] || {};
                        priceValue.amount = +priceValue.amount || 0;
                        priceValue.adjustments = priceValue.adjustments || {};

                        additionalPrice[priceCode] = additionalPrice[priceCode] || {
                            'amount': 0,
                            'adjustments': {}
                        };

                        additionalPrice[priceCode].amount =  0 + (additionalPrice[priceCode].amount || 0) +
                            priceValue.amount;

                        _.each(priceValue.adjustments, function (adValue, adCode) {
                            additionalPrice[priceCode].adjustments[adCode] = 0 +
                                (additionalPrice[priceCode].adjustments[adCode] || 0) + adValue;
                        });
                    }
                });
            });
            if (_.isEmpty(additionalPrice)) {
                this.cache.displayPrices = utils.deepClone(this.options.prices);
            } else {
                _.each(additionalPrice, function (option, priceCode) {
                    origin = this.options.prices[priceCode] || {};
                    finalPrice = prices[priceCode] || {};
                    option.amount = option.amount || 0;

                    origin.amount = origin.amount || 0;
                    origin.adjustments = origin.adjustments || {};
                    finalPrice.adjustments = finalPrice.adjustments || {};
                    finalPrice.amount = 0 + origin.amount + option.amount;
                    _.each(option.adjustments, function (pa, paCode) {
                        finalPrice.adjustments[paCode] = 0 + (origin.adjustments[paCode] || 0) + pa;
                    });

                }, this);
            }
            this.element.trigger('reloadPrice');
        },

        /*eslint-disable no-extra-parens*/
        /**
         * Render price unit block.
         */
        reloadPrice: function reDrawPrices() {
            var self = this;
            var priceChildren = self.calculateChildrenPrices(); // .totalChildren
            var priceMultiplier = self.calculateMultiplierPrices(); // .totalMultiplier   .totalDefaultValues

            var basePrice = self.calculateBasePriceToDiscount();
            var oldPrice = self.calculateOldPriceToDiscount();
            var bookingPrice = self.cache.optionsChildCalcule.bookingPrice;
            var bookingPriceOld = self.cache.optionsChildCalcule.bookingPriceOld;
            if( bookingPrice == 0 ){
                /*If bookin price = 0 take it the base price to calculate with custom options*/
                basePrice = self.cache.optionsChildCalcule.basePrice;
                oldPrice = self.cache.optionsChildCalcule.oldPrice;
            }
            var priceFormat = (this.options.priceConfig && this.options.priceConfig.priceFormat) || {},
                priceTemplate = mageTemplate(this.options.priceTemplate);
            _.each(this.cache.displayPrices, function (price, priceCode) {
                price.final = _.reduce(price.adjustments, function (memo, amount) {
                    return memo + amount;
                }, price.amount);

                // if( priceCode == 'oldPrice' ){
                //     self.createHtmlToOldPrice(0);
                // }

                var priceCalculatedWithBooking = price.final;
                if( priceChildren.totalChildren > 0 || self.cache.optionsChildCalcule.bookingPrice > 0 || self.cache.optionsChildCalcule.bookingPriceOld > 0 ){
                    priceCalculatedWithBooking = price.final + priceChildren.totalChildren + priceMultiplier.totalMultiplier + bookingPrice;
                    priceCalculatedWithBooking -= ( basePrice + priceMultiplier.totalDefaultValues );

                    if( priceCode == 'oldPrice' ){
                        if( self.cache.optionsChildCalcule.bookingPriceOld > 0 ){
                            priceCalculatedWithBooking = price.final + priceChildren.totalChildren + priceMultiplier.totalMultiplier + bookingPriceOld;
                            priceCalculatedWithBooking -= ( oldPrice + priceMultiplier.totalDefaultValues );
                        }
                    }else{

                    }
                }

                if(
                    //Just to transfer products without select booking date
                    rountripUtils.ifExistOptionRoundtrip() &&
                    self.cache.optionsChildCalcule.bookingPrice == 0 &&
                    self.cache.optionsChildCalcule.bookingPriceOld == 0
                ){
                    if( self.cache.optionsChildCalcule.roundtripPrice > 0 || self.cache.optionsChildCalcule.roundtripOldPrice > 0 ){
                        priceCalculatedWithBooking = price.final + self.cache.optionsChildCalcule.roundtripPrice + priceChildren.totalChildren + priceMultiplier.totalMultiplier + bookingPrice;
                        priceCalculatedWithBooking -= ( basePrice + priceMultiplier.totalDefaultValues );
                    }else{

                    }
                }

                /*Get price to multiplier select*/
                var multiplierDropdownTotal = self.calculatePriceFromMultiplierDropdown();
                priceCalculatedWithBooking += multiplierDropdownTotal;
                /*---*/
                /*Get price to multiplier select*/
                var multiplierChildrenDropdownTotal = self.calculateChildrenPricesForDropdown();
                priceCalculatedWithBooking += multiplierChildrenDropdownTotal;
                /*---*/


                price.formatted = utils.formatPrice(priceCalculatedWithBooking, priceFormat);

                $('[data-price-type="' + priceCode + '"]', this.element).html(priceTemplate({
                    data: price
                }));

                self.addSpecialPriceToTransferRoundTip(price,priceCode);

                if( !self.cache.displayPrices.hasOwnProperty('oldPrice') && priceCode == 'finalPrice' && bookingPriceOld > 0){
                    self.addSpecialPriceBookingResult(price);
                }

                if( $('.btn-floating-content').length > 0){
                    $('.btn-floating-content .price').html(priceTemplate({
                        data: price
                    }));
                }

            }, this);
            self.enableDisableButtonAddToCart();
            self.calculateSubTotales();

            const inputCheckbox = document.querySelector(".partial-payment-checkbox input");
            const totalShown = +document.querySelector('span[data-price-type="finalPrice"] .price').textContent.replace( /^\D+/g, '').replaceAll(',','');
            let newTotal = totalShown;
            if (inputCheckbox?.checked == true) {
                const percentDiscount = +document.querySelector(".partial-payment-checkbox .control span").textContent.replace("%", '').replace(/^\D+/, '');
                newTotal = totalShown * (percentDiscount / 100);
                document.querySelector('span[data-price-type="finalPrice"] .price').textContent = utils.formatPrice(newTotal, priceFormat);
            }
        },
        getOldPriceIfExistToCalculatePriceRountripWithoutBooking: function(priceCode){
            var prices = this.cache.displayPrices;
            if (prices.hasOwnProperty('oldPrice') && priceCode == 'oldPrice') {
                return prices.oldPrice.amount;
            }
            return prices.finalPrice.amount;

        },
        addSpecialPriceToTransferRoundTip: function(price,priceCode){
            var self = this;

            if(
                //Just to transfer products without select booking date
                rountripUtils.ifExistOptionRoundtrip() &&
                self.cache.optionsChildCalcule.bookingPrice == 0 &&
                self.cache.optionsChildCalcule.bookingPriceOld == 0
            ){
                if( self.cache.optionsChildCalcule.roundtripOldPrice > 0){
                    if( !$('[data-price-type="oldPrice"]').length){
                        self.createHtmlToOldPrice(0);
                    }
                }

                var _price = self.calculateBasePriceToDiscount();
                //var _oldPrice = self.calculateOldPriceToDiscount();
                var _oldPrice = self.getOldPriceIfExistToCalculatePriceRountripWithoutBooking(priceCode);



                //var oldPrice = (self.calculateOldPriceToDiscount() > 0) ? self.calculateOldPriceToDiscount() : self.calculateBasePriceToDiscount() ;
                //var oldPrice = (oldPrice == 0 ) ? _price : _oldPrice;
                var oldPrice = _oldPrice;
                var priceChildren = self.calculateChildrenPrices(); // .totalChildren
                var priceMultiplier = self.calculateMultiplierPrices(); // .totalMultiplier   .totalDefaultValues
                var priceFormat = (this.options.priceConfig && this.options.priceConfig.priceFormat) || {},
                    priceTemplate = mageTemplate(this.options.priceTemplate);
                var priceCalculatedWithBooking = price.final;
                var bookingPriceOld = self.cache.optionsChildCalcule.bookingPriceOld;

                //var _oldPrice = price.final - (priceMultiplier.totalDefaultValues + priceChildren.totalDefaultValues);

                if( self.cache.optionsChildCalcule.roundtripOldPrice > 0 ){
                    priceCalculatedWithBooking = price.final + self.cache.optionsChildCalcule.roundtripOldPrice + priceChildren.totalChildren + priceMultiplier.totalMultiplier + bookingPriceOld;
                    priceCalculatedWithBooking -= ( oldPrice + priceMultiplier.totalDefaultValues );
                }

                price.formatted = utils.formatPrice(priceCalculatedWithBooking, priceFormat);

                $('[data-price-type="oldPrice"]', this.element).html(priceTemplate({
                    data: price
                }));
                self.cache.optionsChildCalcule.roundtripOldPriceApplied = true;

            }
        },
        addSpecialPriceBookingResult: function(price){
            var self = this;
            if(self.cache.optionsChildCalcule.bookingPriceOld > 0){
                if( !$('[data-price-type="oldPrice"]').length){
                    self.createHtmlToOldPrice(0);
                }



                var _price = self.calculateBasePriceToDiscount();
                var _oldPrice = self.calculateOldPriceToDiscount();


                //var oldPrice = (self.calculateOldPriceToDiscount() > 0) ? self.calculateOldPriceToDiscount() : self.calculateBasePriceToDiscount() ;
                //var oldPrice = (_price > 0 && _oldPrice > 0 ) ? _oldPrice : _price;
                var oldPrice = _price;
                var priceChildren = self.calculateChildrenPrices(); // .totalChildren
                var priceMultiplier = self.calculateMultiplierPrices(); // .totalMultiplier   .totalDefaultValues
                var priceFormat = (this.options.priceConfig && this.options.priceConfig.priceFormat) || {},
                    priceTemplate = mageTemplate(this.options.priceTemplate);
                var priceCalculatedWithBooking = price.final;
                // var bookingPriceOld = self.cache.optionsChildCalcule.bookingPriceOld;

                if( self.cache.optionsChildCalcule.bookingPriceOld > 0 ){
                    priceCalculatedWithBooking = price.final + self.cache.optionsChildCalcule.bookingPriceOld + priceChildren.totalChildren + priceMultiplier.totalMultiplier;
                    priceCalculatedWithBooking -= ( oldPrice + priceMultiplier.totalDefaultValues );
                }

                price.formatted = utils.formatPrice(priceCalculatedWithBooking, priceFormat);

                $('[data-price-type="oldPrice"]', this.element).html(priceTemplate({
                    data: price
                }));
            }
        },

        createHtmlToOldPrice: function(price){
            if( $('[data-price-type="oldPrice"]').length == 0){
                $('.main-form .total .price-box').append(
                    $('<span/>',{'class':'old-price'}).append(
                        $('<span/>',{'class':'price-container price-final_price tax weee'}).append(
                            $('<span/>',{
                                'class':'price-wrapper',
                                'data-price-type' : 'oldPrice',
                                'data-price-amount' : price,
                                'id':'old-price-'
                            }).append(
                                $('<span/>',{'class':'price',text: price})
                            )
                        )
                    )
                );
            }
        },

        /*eslint-enable no-extra-parens*/
        /**
         * Overwrites initial (default) prices object.
         * @param {Object} prices
         */
        setDefault: function setDefaultPrices(prices) {
            this.cache.displayPrices = utils.deepClone(prices);
            this.options.prices = utils.deepClone(prices);
        },

        /**
         * Custom behavior on getting options:
         * now widget able to deep merge of accepted configuration.
         * @param  {Object} options
         * @return {mage.priceBox}
         */
        _setOptions: function setOptions(options) {
            $.extend(true, this.options, options);

            if ('disabled' in options) {
                this._setOption('disabled', options.disabled);
            }

            return this;
        },

        /**
         * setDefaultsFromDataSet
         */
        _setDefaultsFromDataSet: function _setDefaultsFromDataSet() {
            var box = this.element,
                priceHolders = $('[data-price-type]', box),
                prices = this.options.prices;

            this.options.productId = box.data('productId');

            if (_.isEmpty(prices)) {
                priceHolders.each(function (index, element) {
                    var type = $(element).data('priceType'),
                        amount = parseFloat($(element).data('priceAmount'));

                    if (type && !_.isNaN(amount)) {
                        prices[type] = {
                            amount: amount
                        };
                    }
                });
            }
        },

        /**
         * setDefaultsFromPriceConfig
         */
        _setDefaultsFromPriceConfig: function _setDefaultsFromPriceConfig() {
            var config = this.options.priceConfig;

            if (config && config.prices) {
                this.options.prices = config.prices;
            }
        },

        /**
         * Updates product final and base price according to tier prices
         */
        updateProductTierPrice: function updateProductTierPrice() {
            var originalPrice,
                prices = {'prices': {}};

            if (this.options.prices.finalPrice) {
                originalPrice = this.options.prices.finalPrice.amount;
                prices.prices.finalPrice = {'amount': this.getPrice('price') - originalPrice};
            }

            if (this.options.prices.basePrice) {
                originalPrice = this.options.prices.basePrice.amount;
                prices.prices.basePrice = {'amount': this.getPrice('basePrice') - originalPrice};
            }

            this.updatePrice(prices);
        },

        /**
         * Returns price.
         *
         * @param {String} priceKey
         * @returns {Number}
         */
        getPrice: function (priceKey) {
            var productQty = $(this.qtyInfo).val(),
                result,
                tierPriceItem,
                i;

            for (i = 0; i < this.options.priceConfig.tierPrices.length; i++) {
                tierPriceItem = this.options.priceConfig.tierPrices[i];
                if (productQty >= tierPriceItem.qty && tierPriceItem[priceKey]) {
                    result = tierPriceItem[priceKey];
                }
            }

            return result;
        }
    });

    return $.mage.priceBox;
});
