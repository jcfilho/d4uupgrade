define(
    [
        'jquery',
        'lastMinuteOperations'
    ],
    function($,lastMinuteOperations){


        AppDatalastMinuteToForm = function(){

        };
        AppDatalastMinuteToForm.prototype = {
            selectors : {
                containerFormProduct : '.book-tours.book-product-addtocart',
                buttonAdddToCart : '.action.primary.tocart'
            },
            init: function () {
                var self = this;
            },
            assignDataToForm : function(data){
                var self = this;
                $(self.selectors.containerFormProduct).data('lastMinuteData',data);
            },
            clickToAddToCart : function(){
                var self = this;
                $(self.selectors.buttonAdddToCart).on('click',function(event){
                    event.preventDefault();
                    if( !lastMinuteOperations.verifyIfDaySelectedIsCurrentDay('#bk-checkin-checkout') ){
                        $('#product_addtocart_form').submit();
                    }else{
                        if( !$(this).is(':disabled') ){
                            if(lastMinuteOperations.verifyIfIsDayIsAvailable('#bk-checkin-checkout',true)){
                                $('#product_addtocart_form').submit();
                            }
                            return false;
                        }
                    }
                    return true;
                });
            }
        };

        return function (config) {
            var app = new AppDatalastMinuteToForm();
            app.assignDataToForm(config);
            app.clickToAddToCart();
        }

    }
);