define(
    [
        'jquery',
        'moment',
        'moment-timezone-with-data'
    ],
    function($,moment){

        var getCurrentDate = function () {
            return moment().tz("America/Argentina/Buenos_Aires");
        };

        return {
            getDateByStringYearMonthDay : function(dateString){
                //stringDate -> format 2018-10-25
                var tempStartDateSplit = dateString.split("-");
                return new Date(tempStartDateSplit[0],tempStartDateSplit[1]-1,tempStartDateSplit[2],0,0,0,0);
            },
            getCurentDateNativeJavascript : function(){
                var momentDate = getCurrentDate();
                return new Date(momentDate.get('year'), momentDate.get('month'), momentDate.get('date'), momentDate.get('hour'), momentDate.get('minute'), momentDate.get('second'), momentDate.get('millisecond'))
            }
        }
    }
);