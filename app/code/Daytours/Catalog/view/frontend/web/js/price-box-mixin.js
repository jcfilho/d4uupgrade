

/*
*
* DOESN'T USE BECAUSE FIREFOX HAS PROBLEMS WITH MIXING
*
* */



define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'underscore',
    'mage/template',
    'jquery/ui',
    'mage/translate'
], function($, utils, _, mageTemplate,ui,$tr){
    return function(originalWidget){
        $.widget(
            'mage.priceBox',$['mage']['priceBox'],
            {
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

                    box.trigger('updatePrice');
                    this.cache.displayPrices = utils.deepClone(this.options.prices);
                    this.cache.optionsChildCalcule.basePrice = this.options.prices.finalPrice.amount;
                    this.changeQty();
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
                changeBookingPrice: function (price) {
                    console.log('change-booking-price');
                    var self = this;
                    this.cache.optionsChildCalcule.bookingPrice = price;
                    self.updatePrice({});
                },
                calculateChildrenPrices: function(){
                    var total = 0,
                        self = this;
                    if( !_.isEmpty(self.getQty()) && self.getChildActive()){
                        if( $.isNumeric(self.cache.optionsChildCalcule.qtySelected) && _.size(self.cache.optionsChildCalcule.pricesFound) > 0 ){
                            _.each(self.cache.optionsChildCalcule.pricesFound, function(price){
                                total = total + (price * self.cache.optionsChildCalcule.qtySelected);
                            });
                        }
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
                calculateBasePrice: function(){
                    var self = this;
                    var qtyAdults = self.getQty();
                    return (qtyAdults * self.cache.optionsChildCalcule.basePrice) - self.cache.optionsChildCalcule.basePrice;
                },
                getBasePriceToMultiplier: function(){
                    var self = this;
                    if( self.isMultiplierProduct() ){
                        return self.cache.optionsChildCalcule.basePrice;
                    }else{
                        return 0;
                    }

                },
                /**
                 * Get label multiplier by element
                 */
                getLabelMultiplier: function(element){
                    var elementMultiplier = $(element);
                    return elementMultiplier.parents('.field').find('.label span').html();
                },
                buildNewElementSubTotal: function(label,price,qty){
                    return '<p><b> ' + label +  ' </b> <span>' + utils.formatPrice(price) + ' x ' + qty + '</span></p>';
                },
                /**
                 * Calculate total from options multiplier
                 */
                calculateSubTotales: function(){
                    var hasOptions  = false;
                    var multipliers = $('#product_addtocart_form .isMultiplier1:checked');
                    var child       = $('#product_addtocart_form .isChild1');
                    var self        = this;
                    var containerSubTotal       = $('.sub-total-price-regular');
                    var containerSubTotalsAll   = $('.sub-total-price-regular .sub-total-content');

                    var childElements = [];

                    containerSubTotal.removeClass('show').addClass('hide');
                    containerSubTotalsAll.html('');
                    var dataSerialize = [];

                    /*Set adults value*/
                    var qtyAdults = self.getQty();
                    var labelAdults = $tr('Adults');
                    var priceBaseBooking = self.cache.optionsChildCalcule.bookingPrice / qtyAdults;
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
                    /*------*/

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

                    // console.log(childElements);

                    if( hasOptions ){
                        containerSubTotal.removeClass('hide').addClass('show');
                        if(child.length){
                            var optionSelected = child.find('option:selected');
                            if( optionSelected.val() !== '' && optionSelected.text() !== 'no'  && optionSelected.text() !== 'No'){

                                /*Adding element children to detail*/
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
                                /*------------*/


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

                    $('body #extra-options-rs').val(JSON.stringify(dataSerialize));
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
                            if( priceCode == 'isMultiplier' && additional[priceCode]['result'] == '1'){
                                isMultiplier = true;
                            }
                            if( priceCode == 'isChild' && additional[priceCode]['result'] == '1'){
                                isChild = true;
                            }
                        });

                        _.each(pricesCode, function (priceCode) {
                            if( priceCode != 'isMultiplier' && priceCode != 'isChild' ){
                                priceValue = additional[priceCode] || {};
                                priceValue.amount = +priceValue.amount || 0;
                                var newTotal = priceValue.amount;
                                priceValue.adjustments = priceValue.adjustments || {};

                                additionalPrice[priceCode] = additionalPrice[priceCode] || {
                                    'amount': 0,
                                    'adjustments': {}
                                };

                                if( isMultiplier ){
                                    var priceFromDatabase = priceValue.amount;
                                    newTotal = qtyAdults * priceFromDatabase;
                                }



                                if( priceCode == 'childPrice' && $.isNumeric(priceValue.amount)){
                                    var childPriceDatabase = priceValue.amount;
                                    pricesChild.push(childPriceDatabase);
                                    self.cache.optionsChildCalcule.pricesFound.push(childPriceDatabase);
                                }

                                priceValue.amount = +priceValue.amount || 0;
                                additionalPrice[priceCode].amount =  0 + (additionalPrice[priceCode].amount || 0) + newTotal;

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
                reloadPrice: function reDrawPrices() {
                    var self = this;
                    var priceChildren = self.calculateChildrenPrices();
                    var basePrice = 0; //self.calculateBasePrice();
                    var basePriceToMultiplier = self.getBasePriceToMultiplier();
                    var bookingPrice = self.cache.optionsChildCalcule.bookingPrice;
                    if( bookingPrice == 0 ){
                        /*If bookin price = 0 take it the base price to calculate with custom options*/
                        basePrice = self.cache.optionsChildCalcule.basePrice;
                    }
                    var priceFormat = (this.options.priceConfig && this.options.priceConfig.priceFormat) || {},
                        priceTemplate = mageTemplate(this.options.priceTemplate);
                    _.each(this.cache.displayPrices, function (price, priceCode) {
                        price.final = _.reduce(price.adjustments, function (memo, amount) {
                            return memo + amount;
                        }, price.amount);

                        price.formatted = utils.formatPrice(((price.final + priceChildren + basePrice + bookingPrice) - basePriceToMultiplier), priceFormat);
                        $('[data-price-type="' + priceCode + '"]', this.element).html(priceTemplate({
                            data: price
                        }));
                    }, this);
                    self.calculateSubTotales();
                }
            });

        return $['mage']['priceBox'];
    };
});