define(
    [
        'jquery'
    ],function($){

        var AppUtilsRountrip = {
            selectors : {
                priceBox : '[data-role="priceBox"]',
                containerFormProduct : '.book-tours.book-product-addtocart',
                optionRoundtrip : 'body #option-roundtrip',
                inputCalendarTwoRoundtrip : '#bk-checkin-checkout-two'
            },
            diferentToUndefined : function(data){
                if( typeof data !== 'undefined'){
                    return true;
                }
                return false;
            },
            getvalueifIsPosibleOrCero: function(data){
                return (this.diferentToUndefined(data)) ? data : 0;
            },
            dataRountripPrice: function () {
                var data = $(this.selectors.containerFormProduct).data('roundtrip_price_data');
                if( this.diferentToUndefined(data)){
                    return {
                        rountrip_price : this.getvalueifIsPosibleOrCero(data.rountrip_price),
                        rountrip_old_price: this.getvalueifIsPosibleOrCero(data.rountrip_old_price)
                    };
                }
                return false;
            }
        };

        return {
            changeToPriceRountrip: function () {
                var dataPriceRountrip = AppUtilsRountrip.dataRountripPrice();
                if( dataPriceRountrip ){
                    var priceData = $(AppUtilsRountrip.selectors.priceBox).data();
                    if( AppUtilsRountrip.diferentToUndefined(priceData.magePriceBox) && AppUtilsRountrip.diferentToUndefined(priceData.magePriceBox.changeBookingPrice)  ){
                        priceData.magePriceBox.enableOrDisablePriceRountrip(dataPriceRountrip.rountrip_old_price,dataPriceRountrip.rountrip_price);
                    }
                }
                return true;
            },
            changeToPriceGoing:function(){
                var priceData = $(AppUtilsRountrip.selectors.priceBox).data();
                if( AppUtilsRountrip.diferentToUndefined(priceData.magePriceBox) && AppUtilsRountrip.diferentToUndefined(priceData.magePriceBox.changeBookingPrice)  ){
                    priceData.magePriceBox.enableOrDisablePriceGoing(0,0);
                }
            },
            ifExistOptionRoundtrip: function () {
                if( $(AppUtilsRountrip.selectors.optionRoundtrip).length > 0 ){
                    if( $(AppUtilsRountrip.selectors.optionRoundtrip).is(':checked') ) {
                        return true;
                    }else {
                        return false;
                    }
                }
                return false;
            },
            ifCalendarTwohasDateSelected : function () {
                if( $(AppUtilsRountrip.selectors.inputCalendarTwoRoundtrip).val() == '' ){
                    return false;
                }
                return true;
            }
        };

    }
);