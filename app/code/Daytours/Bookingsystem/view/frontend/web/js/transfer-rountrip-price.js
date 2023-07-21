define(
    [
        'jquery'
    ],
    function($){


        AppDataRountripPrice = function(){

        };
        AppDataRountripPrice.prototype = {
            selectors : {
                containerFormProduct : '.book-tours.book-product-addtocart'
            },
            init: function () {
                var self = this;
            },
            assignDataToForm : function(data){
                var self = this;
                $(self.selectors.containerFormProduct).data('roundtrip_price_data',data);
            }
        };

        return function (config) {
            var app = new AppDataRountripPrice();
            app.assignDataToForm(config);
        }

    }
);