define(
    [
        'jquery',
        'moment',
        'Magento_Customer/js/customer-data',
        'mage/translate'
    ],
    function($,moment,customerData){

        var dataLastMinuteProduct = $('.book-tours.book-product-addtocart').data();
        var tooltipLastMinute = $('.book-tours-form .simple-booking-form-input .tooltip-box');
        var importantLastMinute = $('.book-tours-form .product-info-main .important');

        var getDataCurrentDate = function(){
            return moment().tz("America/Argentina/Buenos_Aires");
        };

        var ifIsToday = function(elementDatpicker){
            var currentDay = getDataCurrentDate();
            var currentDayFormat = currentDay.format('DD/MM/YYYY');
            var dateSelected = $(elementDatpicker).val();

            if( dateSelected == currentDayFormat ){
                return true;
            }
            return false;
        };
        var ifIsTomorrow = function(elementDatpicker){
            var tomorrow = getDataCurrentDate();
            tomorrow.add(1,'days');
            var tomorrowFormat = tomorrow.format('DD/MM/YYYY');
            var dateSelected = $(elementDatpicker).val();

            if( dateSelected == tomorrowFormat ){
                return true;
            }
            return false;
        };

        var ifDaySelectedIsTodayOrTomorrow = function (elementDatpicker) {
            if( ifIsToday(elementDatpicker) || ifIsTomorrow(elementDatpicker) ){
                return true;
            }
            return false;
        };

        var ifHourSelectedIsBetweenRangeToLastMinute = function (elementDatpicker) {
            var lastMinuteEventStart = dataLastMinuteProduct.lastMinuteData.lastminute_event_start;
            var lastMinuteEventStartSplited = lastMinuteEventStart.split(':');

            if (ifIsTomorrow(elementDatpicker)){
                return true;
            }

            var date = getDataCurrentDate(); //current date time zone
            var hour = date.hour(); //current hour
            var minute = date.minute(); //current minute

            if( hour <= lastMinuteEventStartSplited[0] ){
                if( hour == lastMinuteEventStartSplited[0] ){
                    if( minute <= lastMinuteEventStartSplited[1] ){
                        return true;
                    }else{
                        return false;
                    }
                }
                return true;
            }
            return false;

        };

        var ifLastMinuteIsAvailable = function () {
            if( typeof $('.book-tours.book-product-addtocart').data('lastMinuteData') !== 'undefined' ){
                if(
                    typeof dataLastMinuteProduct.lastMinuteData.lastminute_event_start !== 'undefined' &&
                    dataLastMinuteProduct.lastMinuteData.lastminute_event_start !== null
                ){
                    return true;
                }
            }
            return false;
        };

        var showAndHideElementsLastMinute = function(option){
            if( option == 1 ){
                importantLastMinute.show();
                tooltipLastMinute.show();
            }

            if( option == 2 ){
                importantLastMinute.hide();
                tooltipLastMinute.hide();
            }
        };

        return {

            verifyIfIsDayIsAvailable: function(elementDatpicker,clickFromAddToCart = false){

                // If the hour is grater that start event configuration the day will be desactived
                if( ifLastMinuteIsAvailable() )
                {
                    var lastMinuteEventStart = dataLastMinuteProduct.lastMinuteData.lastminute_event_start;
                    var lastMinuteEventStartSplited = lastMinuteEventStart.split(':');

                    var currentDate = getDataCurrentDate();

                    var disableCurrentDay = false;
                     if( !ifIsTomorrow(elementDatpicker) ){
                        if( currentDate.hour() > lastMinuteEventStartSplited[0]){
                            disableCurrentDay = true;
                        }

                        if( currentDate.hour() == lastMinuteEventStartSplited[0]){
                            if( currentDate.minute() > lastMinuteEventStartSplited[1]){
                                disableCurrentDay = true;
                            }
                        }
                     }

                    if( disableCurrentDay ){
                        var calendarBooking = $(elementDatpicker).data('daterangepicker').container;
                        calendarBooking.find('td.today')
                            .removeClass('active start-date end-date available')
                            .addClass('off disabled');
                        $('.action.primary.tocart').prop('disabled',true);
                        if( clickFromAddToCart ){
                            //Click from Add To Cart Button when the last minute was available but the time expired meanwhile the customer configured other parameters or anythink (Validation a few minutes ago)
                            $(elementDatpicker).val('');
                            showAndHideElementsLastMinute(2);
                            var nameProduct = $('.page-title-wrapper.product .page-title span').html();
                            var messageError = $.mage.__('%s has already started.').replace('%s', nameProduct);
                            customerData.set('messages', {
                                messages: [{
                                    type: 'error',
                                    text: messageError
                                }]
                            });

                            var scrollValue = $(".page-title-wrapper.product").offset().top - 200;
                            $('html, body').animate({
                                scrollTop: scrollValue
                            }, 800);
                        }


                        return false;

                    }else{
                        return true;
                    }
                }
            },
            showInformationLastMinute: function(elementDatpicker){
                if( ifLastMinuteIsAvailable() &&
                    ifDaySelectedIsTodayOrTomorrow(elementDatpicker) &&
                    ifHourSelectedIsBetweenRangeToLastMinute(elementDatpicker)
                )
                {
                    showAndHideElementsLastMinute(1);
                }else{
                    showAndHideElementsLastMinute(2);
                }
            },
            verifyIfDaySelectedIsCurrentDay: function(elementDatpicker){
                return ifDaySelectedIsTodayOrTomorrow(elementDatpicker);
            },
            isProductLastMinute: function(){
                return ifLastMinuteIsAvailable();
            },
            changeData: function () {

                var date = moment();
                var hour = date.hour();
                var minute = date.minute();

                var dayPlusOne = date.add(1,'days');
                var dayPLusOneFormat = dayPlusOne.format('DD/MM/YYYY'); // day/month/year
                console.log(dayPlusOne.format('DD/MM/YYYY'));

            }
        }
    }
);