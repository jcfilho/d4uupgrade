define([
    'jquery',
    'Magento_Catalog/js/price-utils'
], function($,utils){

    var mageJsComponent = function()
    {
        var methods = {
            _init: function(){
                var self = this;
                self.buildTooltipValues();
            },
            setInitialValuesToSelect :function(){
                var contentTooltip = $('.isMultiplierSelect1').parents('.option-multiplier').find('.tooltip-multiplier .info-tooltip-content');
                var formatPrice = utils.formatPrice(0);
                var formatPriceChild = utils.formatPrice(0);

                contentTooltip.find('.tt-option').html(formatPrice);
                contentTooltip.find('.tt-child').html(formatPriceChild);

            },
            buildTooltipValues: function(){
                var self = this;
                if( $('.isMultiplier1').length > 0 ){
                    var multiplicadores = $('.isMultiplier1');
                    $.each(multiplicadores,function(index,item){
                        var price = $(item).attr('price');
                        var priceChild = $(item).attr('price-child');
                        if( price != '0' || priceChild != '0'){
                            var contentTooltip = $(item).parents('.option-multiplier').find('.tooltip-multiplier .info-tooltip-content');
                            var formatPrice = utils.formatPrice(price);
                            var formatPriceChild = utils.formatPrice(priceChild);
                            contentTooltip.find('.tt-option').html(formatPrice);
                            contentTooltip.find('.tt-child').html(formatPriceChild);
                        }
                    });
                }

                if( $('.isMultiplierSelect1').length > 0 ){
                    self.setInitialValuesToSelect();

                    $('.isMultiplierSelect1').on('change',function(){

                        var formatPrice = utils.formatPrice(0);
                        var formatPriceChild = utils.formatPrice(0);
                        var selectedValue = $(this).val();
                        if( selectedValue != '' && selectedValue != 'no' && selectedValue != 'No' ){
                            var price = $(this).find(":selected").attr('price');
                            var priceChild = $(this).find(":selected").attr('price-child');
                            if( price != '0' || priceChild != '0'){
                                formatPrice = utils.formatPrice(price);
                                formatPriceChild = utils.formatPrice(priceChild);
                            }
                        }
                        var contentTooltip = $(this).parents('.option-multiplier').find('.tooltip-multiplier .info-tooltip-content');

                        contentTooltip.find('.tt-option').html(formatPrice);
                        contentTooltip.find('.tt-child').html(formatPriceChild);
                    });
                }
            }
        };

        methods._init();
    };

    return mageJsComponent;
});