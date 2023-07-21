define(
    ['jquery']
    ,function($){

        AppDataTransferLimit = function(){

        };
        AppDataTransferLimit.prototype = {
            selectors : {
                containerFormProduct : '.book-tours.book-product-addtocart',
                containerProductInfoMain : '.product-info-main',
                optionGoing: '#option-going',
                optionRountrip : '#option-roundtrip',
                qty : '#qty',
                btnNextQty :'.product-info-main .field.qty .btn-number2'
            },
            init: function () {
                var self = this;
            },
            assignDataToForm : function(data){
                var self = this;
                $(self.selectors.containerFormProduct).data('limitTransfer',data);
                self.clickOptionTransfer();
                self.changeLimitByOption(1);
            },
            clickOptionTransfer: function(){
                var self = this;
                $(self.selectors.containerProductInfoMain).on('change',self.selectors.optionGoing,function(){
                    self.changeLimitByOption(1);
                });
                $(self.selectors.containerProductInfoMain).on('change',self.selectors.optionRountrip,function(){
                    self.changeLimitByOption(2);
                });
            },
            changeLimitByOption : function (option){
                var self = this;
                if( typeof $(self.selectors.containerFormProduct).data('limitTransfer') !== 'undefined' ){
                    var dataFrom = $(self.selectors.containerFormProduct).data('limitTransfer');
                    var limit_going = dataFrom.limit_going;
                    var limit_rountrip = dataFrom.limit_rountrip;
                    var currentQty = $(self.selectors.qty).val();
                    var limitCurrentOptionSelecte = 0;

                    if( option == 1 ){
                        if( limit_going > 0 ){
                            $(self.selectors.qty).attr('max',limit_going);
                            limitCurrentOptionSelecte = limit_going;
                            if( currentQty < limit_going ){
                                $(self.selectors.btnNextQty).removeAttr('disabled');
                            }
                        }
                    }else{
                        if( limit_rountrip > 0 ){
                            $(self.selectors.qty).attr('max', limit_rountrip);
                            limitCurrentOptionSelecte = limit_rountrip;
                            if( currentQty < limit_rountrip ){
                                $(self.selectors.btnNextQty).removeAttr('disabled');
                            }
                        }
                    }

                    if( currentQty > limitCurrentOptionSelecte ){
                        if( limitCurrentOptionSelecte > 0 ){
                            $(self.selectors.qty).val(limitCurrentOptionSelecte).trigger('change');
                        }
                    }

                    if( limitCurrentOptionSelecte <= 0 ){
                        $(self.selectors.qty).attr('max',10000000);
                    }
                }
            }
        };

        return function (config) {
            var app = new AppDataTransferLimit();
            app.assignDataToForm(config);
        }
    });

