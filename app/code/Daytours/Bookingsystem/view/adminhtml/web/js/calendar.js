define([
    'jquery',
    'moment',
    'datesHelper',
    'moment-timezone-with-data'
], function($,moment,datesHelper){



    $.fn.MagebayAnyBooking = function(objData)
    {

        $(this).data('objData',objData);

        var getCurrentDay = function(){
            var momentDate = moment().tz("America/Argentina/Buenos_Aires");
            var curDate = new Date(momentDate.get('year'), momentDate.get('month'), momentDate.get('date'), momentDate.get('hour'), momentDate.get('minute'), momentDate.get('second'), momentDate.get('millisecond'))

            return curDate;
        };
        var Data = {
            'DataURL': 'load-calendar.php',
            'booking_id': 0,
            'calendar_number' : 1,
            'booking_type' : '1',
            'obj_status_text' : {
                'available': 'Available',
                'special': 'Special',
                'block': 'Block',
                'unavailable': 'Unavailable',
            },
            'currency' : '$',
            'booking_time' : '1',
            'booking_label' : 'Booking Calendar',
            'name_day_th' : ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'],
            'name_day_short_th' : ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
            'name_day_shortest_th' : ['Mo','Tu','We','Th','Fi','Sa','Su'],
            'add_text' : 'Add',
            'remove_text' : 'Remove',
            'next_text' : 'Next',
            'pre_text' : 'Pre',
            'url_add_item' : 'add.php',
            'str_current_date' : '',
            'disable_day' : '',
            'booking_time' : '1',
        }
        Schedules  = {};
        LockedDates  = {};
        bookingOrders  = {};
        FirstDay = 1;
        contentCalendar = $('.item-one #booking-calendar');
        // curDate = new Date();
        curDate = getCurrentDay();
        curMonth = curDate.getMonth() + 1;
        curYear = curDate.getFullYear();
        curMonthText = curMonth > 9 ? curMonth : '0'+curMonth;
        curDayText = curDate.getDate() > 9 ? curDate.getDate() : '0'+curDate.getDate();
        curDateText = curYear+'-'+curMonthText+'-'+curDayText;
        numberMonth = 1;
        dayClick = '';
        DataURL = 'load-calendar.php';
        Schedule = {};
        objStatusText = {
            'available': 'Available',
            'special': 'Special',
            'block': 'Block',
            'unavailable': 'Unavailable',
        };
        currency = '$';
        nameDayTh = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        nameDaySorthTh = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
        nameDayShortestTh = ['Mo','Tu','We','Th','Fi','Sa','Su'];
        bookingLabel = 'Booking Calendar';
        bookingId = 0;
        bookingType = '1';
        addText = 'Add';
        removeText = 'Remove';
        nextText = 'Next';
        preText = 'Pre';
        urlAddItem = 'add.php';
        arDisableDays = [];
        bookingTime = '1'
        methods = {
            init : function()
            {
                if(objData)
                {
                    $.extend(Data,objData);
                }
                methods.parseData()
                $(window).bind('resize',methods.initRPR);
            },
            parseData : function()
            {
                DataURL = Data['DataURL'];
                bookingId = Data['booking_id'];
                calendarNumber = Data['calendar_number'];
                bookingType = Data['booking_type'];
                currency = Data['currency'];
                objStatusText = Data['obj_status_text'];
                nameDayTh = Data['name_day_th'];
                nameDaySorthTh = Data['name_day_short_th'];
                nameDayShortestTh = Data['name_day_shortest_th'];
                bookingLabel = Data['booking_label'];
                addText = Data['add_text'];
                removeText = Data['remove_text'];
                nextText = Data['next_text'];
                preText = Data['pre_text'];
                urlBooking = Data['url_booking'];
                bookingTime = Data['booking_time'];
                urlAddItem = Data['url_add_item'];
                if(Data['str_current_date'] != '')
                {
                    curDateText = Data['str_current_date'];
                    curDate = new Date(Data['str_current_date']);
                }
                curDate = getCurrentDay();
                if(Data['disable_day'] != '')
                {
                    var tempDisableDay = Data['disable_day'];
                    arDisableDays = tempDisableDay.split(',');
                }
                if(bookingType == 'hotel')
                {
                    bookingTime = '1';
                }
                methods.parseCalendarData();
            },

            parseCalendarData : function()
            {
                //get data from database
                $.post(DataURL, {
                    booking_id:bookingId,
                    booking_type: bookingType,
                    calendar_number : calendarNumber
                }, function(data){
                    Schedules = data['data_calendar'];
                    LockedDates =  data['locked_dates']
                    bookingOrders = data['data_order'];
                    methods.initCalendar();
                });
            },
            initCalendar : function()
            {
                var arrayContent = new Array();
                arrayContent.push('<div class="magebay-booking-calendar">');
                arrayContent.push('		<div class="booking-calendar-header">');
                arrayContent.push('			<div class="calendar-header-btn">');
                arrayContent.push('				<div id="calendar-header-left">');
                arrayContent.push('					<div id="calendar-header-btn-add-month" class="calendar-header-btn-add-month" title="'+addText+'">'+addText+'</div>');
                arrayContent.push('					<div id="calendar-header-btn-romove-month" class="calendar-header-btn-romove-month" title="'+removeText+'">'+removeText+'</div>');
                arrayContent.push('				</div>');
                arrayContent.push('				<div id="calendar-header-center">');
                arrayContent.push('					<div id="calendar-header-btn-text">'+bookingLabel)
                arrayContent.push('						<div class="first-month">'+curMonth+' - '+curYear+'</div>');
                arrayContent.push('					</div>');
                arrayContent.push('				</div>');
                arrayContent.push('				<div id="calendar-header-right">');
                arrayContent.push('					<div id="calendar-header-btn-next" class="calendar-header-btn-next-one" title="'+nextText+'">'+nextText+'</div>');
                arrayContent.push('					<div id="calendar-header-btn-pre" class="calendar-header-btn-pre-one" title="'+preText+'">'+preText+'</div>');
                arrayContent.push('				</div>');
                arrayContent.push('				<div class="clear"></div>');
                arrayContent.push('			</div>');
                arrayContent.push('			<div class="calendar-header-week">');
                arrayContent.push('				<div class="calendar-header-day"></div>');
                arrayContent.push('				<div class="calendar-header-day"></div>');
                arrayContent.push('				<div class="calendar-header-day"></div>');
                arrayContent.push('				<div class="calendar-header-day"></div>');
                arrayContent.push('				<div class="calendar-header-day"></div>');
                arrayContent.push('				<div class="calendar-header-day"></div>');
                arrayContent.push('				<div class="calendar-header-day"></div>');
                arrayContent.push('			</div>');
                arrayContent.push('		</div>');
                arrayContent.push('		<div class="booking-calendar-content-one">');
                arrayContent.push('		</div>');
                arrayContent.push('</div>');
                contentCalendar.html(arrayContent.join(''));
                methods.intMonths(curYear,curMonth,'');
                methods.defaultEvent();
                methods.initRPR();
            },
            initRPR: function()
            {
                methods.rsContener();
                methods.rsMonth();
                methods.rsDay();
                methods.rsWeek();
            },
            rsContener: function()
            {
                var width = $(contentCalendar).width();
                if(width < 351)
                {
                    $('.booking-calendar-header').css('height','80px');
                }
                else
                {
                    $('.booking-calendar-header').css('height','60px');
                }
            },
            rsMonth : function()
            {

            },
            rsDay: function()
            {
                var widthTotal = parseInt($('.booking-item-month',contentCalendar).width());
                var WpadingLeft = parseInt($('.booking-item-month',contentCalendar).css('padding-left'));
                var WpadingRight = parseInt($('.booking-item-month',contentCalendar).css('padding-right'));
                widthTotal = widthTotal - (WpadingLeft + WpadingRight);
                var itemDayWith = widthTotal / 7;
                itemDayWith = itemDayWith - 1;
                var itemDayWithContent = itemDayWith - 2;
                $('.booking-item-day').css('width',itemDayWith+'px');
                $('.booking-item-day-content').css('width',itemDayWithContent+'px');
            },
            rsWeek : function()
            {
                var width = $(contentCalendar).width();
                var no = 0;
                $('.calendar-header-day',contentCalendar).each(function(){
                    if(width < 225)
                    {
                        $(this).html(nameDayShortestTh[no]);
                    }
                    else if(width < 570)
                    {
                        $(this).html(nameDaySorthTh[no]);
                    }
                    else
                    {
                        $(this).html(nameDayTh[no]);
                    }
                    no++;
                });
            },
            intMonths : function(year,month,monthClass)
            {

                var noDays = new Date(year,month,0,0,0,0,0).getDate();
                var noDaysPerivous = new Date(year,month-1,0).getDate();
                var firstDay = new Date(year, month-1, 2-FirstDay).getDay();
                var lastDay = new Date(year, month-1, noDays-FirstDay+1).getDay();
                var arrayMonth = new Array();
                if((numberMonth > 1 && month != curMonth) || (numberMonth > 1 && month == curMonth && year != curYear))
                {
                    arrayMonth.push('<div class="month-lable">'+month+'-'+year+'</div>');
                }
                arrayMonth.push('<div class="booking-item-month '+monthClass+'">');
                var start = firstDay;
                if(firstDay == 0)
                    start = 7;
                var classDay = '';
                //day pre month
                for(var i = start - 1; i >= 0; i--)
                {
                    if(month != curMonth)
                    {
                        classDay = 'booking-day-none';
                    }
                    else
                    {
                        classDay = 'booking-day-past';
                    }
                    var dayHhtml = methods.intDays(0,year,month -1 ,noDaysPerivous - i,classDay);
                    arrayMonth.push(dayHhtml);
                }
                //day of current month
                for(var j = 1; j <= noDays; j++)
                {
                    classDay = '';
                    var dayHhtml = methods.intDays(1,year,month,j,classDay);
                    arrayMonth.push(dayHhtml);
                }
                for(var k =  1; k < 7 - lastDay; k++)
                {
                    if(month == curMonth + numberMonth - 1)
                    {
                        classDay = 'booking-day-next-month';
                    }
                    else
                    {
                        classDay = 'booking-day-none';
                    }
                    var dayHhtml = methods.intDays(2,year,month + 1,k,classDay);
                    arrayMonth.push(dayHhtml);
                }
                arrayMonth.push('<div class="clear"></div>');
                arrayMonth.push('</div>');
                $('.booking-calendar-content-one').append(arrayMonth.join(''));
                methods.intEventDay();
            },
            intDays : function(day_id,year,month,day,classDay)
            {
                curDate = getCurrentDay();
                var strClassDay = classDay;
                //var dayObj = new Date(year,month,day);
                //var currentDate = new Date(curDate.getFullYear(),curDate.getMonth() + 1,curDate.getDate());
                var strDay = day >= 10 ? day : '0'+day;
                var strMonth = month >= 10 ? month : '0'+month;
                var dayObj = new Date(year,strMonth - 1,strDay,0,0,0,0);
                var dayId = day_id+'_'+year+'-'+strMonth+'-'+strDay;
                var key_day_id = year+'-'+strMonth+'-'+strDay;
                var price = "";
                var promo = "";
                var qty = "";
                var dataOfDay = {
                    'status' :'',
                    'price' : '',
                    'promo' : '',
                    'qty' : '',
                    'status_text' : '',
                    'status_class' : ''
                };
                var strSatus = ""
                var dateTextMonth = dayObj.getMonth() + 1;
                dateTextMonth = dateTextMonth < 10 ? '0'+dateTextMonth : dateTextMonth;
                dateTextDay = dayObj.getDate() < 10 ? '0'+dayObj.getDate() : dayObj.getDate();
                var dateText = dayObj.getFullYear()+'-'+dateTextMonth+'-'+dateTextDay;
                var availableQty = 0;
                if(dayObj.getTime() < curDate.getTime() && dateText != curDateText)
                {
                    strClassDay = 'booking-day-past';
                }
                else
                {
                    if(Schedules)
                    {
                        for(var keyDay in Schedules)
                        {
                            if(isNaN(keyDay))
                                continue;
                            Schedule = Schedules[keyDay];
                            if(Schedule['default_value'] == '2')
                            {
                                var strDayStart = Schedule['start_date'];
                                var strDayEnd = Schedule['end_date'];
                                var arDayStart = strDayStart.split('-');
                                var arDayEnd = strDayEnd.split('-');
                                //var objDayStart = new Date(arDayStart[0],arDayStart[1],arDayStart[2]);
                                var objDayStart = datesHelper.getDateByStringYearMonthDay(Schedule['start_date']);
                                //var objEndDate = new Date(arDayEnd[0],arDayEnd[1],arDayEnd[2]);
                                var objEndDate = datesHelper.getDateByStringYearMonthDay(Schedule['end_date']);
                                if(dayObj.getTime() < objDayStart.getTime() || dayObj.getTime() > objEndDate.getTime())
                                {
                                    continue;
                                }
                                if(dayObj.getTime() >= objDayStart.getTime() && dayObj.getTime() <= objEndDate.getTime())
                                {
                                    if(bookingTime == '3')
                                    {
                                        var timeSlots = {};
                                        Schedule['qty'] = 0;
                                        if(Schedule.inter_value)
                                        {
                                            var timeSlots = Schedule.inter_value;
                                            for(var keyT in timeSlots)
                                            {
                                                if(!timeSlots[keyT].intervalhours_days || timeSlots[keyT].intervalhours_days == '')
                                                {
                                                    continue;
                                                }
                                                var dateStartSlot = datesHelper.getDateByStringYearMonthDay(timeSlots[keyT].intervalhours_check_in);
                                                var dateEndtSlot = datesHelper.getDateByStringYearMonthDay(timeSlots[keyT].intervalhours_check_out);
                                                var allowTimeSlotDays = timeSlots[keyT].intervalhours_days;
                                                //allowTimeSlotDays = allowTimeSlotDays.split(',');
                                                if(dateStartSlot.getTime() <= dayObj.getTime() && dateEndtSlot.getTime() >= dayObj.getTime() && allowTimeSlotDays.indexOf(''+dayObj.getDay()) !== -1)
                                                {
                                                    Schedule['qty'] += parseInt(timeSlots[keyT].intervalhours_quantity);
                                                }
                                            }
                                        }
                                        dataOfDay['status'] = Schedule['status'];
                                        if(Schedule['qty'] == 0)
                                        {
                                            dataOfDay['status'] = 'block';
                                        }
                                    }
                                    dataOfDay['status'] = Schedule['status'];
                                    dataOfDay['status_class'] = 'day-'+Schedule['status'];
                                    if(Schedule['status'] == 'block' || Schedule['status'] == 'unavailable')
                                    {
                                        dataOfDay['status_text'] = objStatusText[Schedule['status']];
                                    }
                                    else if(Schedule['status'] == 'special' || Schedule['status'] == 'available')
                                    {
                                        if($.inArray(''+dayObj.getDay(), arDisableDays) != -1)
                                        {
                                            dataOfDay['status'] = 'unavailable';
                                            dataOfDay['status_class'] = 'day-unavailable';
                                            dataOfDay['status_text'] = objStatusText[dataOfDay['status']];
                                        }
                                        else
                                        {
                                            let dateToWork = moment(dayObj).format('YYYY-MM-DD');
                                            if( $.inArray(dateToWork,LockedDates) !== -1 ){
                                                dataOfDay['status'] = 'unavailable';
                                                dataOfDay['status_class'] = 'day-unavailable';
                                                dataOfDay['status_text'] = '';
                                            }else{
                                                dataOfDay['status_text'] = objStatusText[Schedule['status']];
                                                dataOfDay['price'] = Schedule['price'];
                                                dataOfDay['promo'] = Schedule['promo'];
                                                dataOfDay['qty'] = Schedule['qty'];
                                            }
                                        }

                                    }
                                    break;
                                }
                            }
                            else if(Schedule['default_value'] == '1')
                            {
                                dataOfDay['status'] = Schedule['status'];
                                dataOfDay['status_class'] = 'day-'+Schedule['status'];
                                dataOfDay['status_text'] = objStatusText[Schedule['status']];
                                if(Schedule['status'] == 'special' || Schedule['status'] == 'available')
                                {
                                    if($.inArray(''+dayObj.getDay(), arDisableDays) != -1)
                                    {
                                        dataOfDay['status'] = 'unavailable';
                                        dataOfDay['status_class'] = 'day-unavailable';
                                        dataOfDay['status_text'] = objStatusText[dataOfDay['status']];
                                    }
                                    else
                                    {
                                        if(bookingTime == '3')
                                        {
                                            var timeSlots = {};
                                            Schedule['qty'] = 0;
                                            if(Schedule.inter_value)
                                            {
                                                var timeSlots = Schedule.inter_value;
                                                for(var keyT in timeSlots)
                                                {
                                                    if(!timeSlots[keyT].intervalhours_days || timeSlots[keyT].intervalhours_days == '')
                                                    {
                                                        continue;
                                                    }
                                                    var allowTimeSlotDays = timeSlots[keyT].intervalhours_days;
                                                    //allowTimeSlotDays = allowTimeSlotDays.split(',');
                                                    if(timeSlots[keyT].intervalhours_check_in == null && timeSlots[keyT].intervalhours_check_out == null && allowTimeSlotDays.indexOf(''+dayObj.getDay()) !== -1)
                                                    {
                                                        Schedule['qty'] += parseInt(timeSlots[keyT].intervalhours_quantity);
                                                    }
                                                }
                                            }
                                            dataOfDay['status'] = Schedule['status'];
                                            if(Schedule['qty'] == 0)
                                            {
                                                dataOfDay['status'] = 'block';
                                            }
                                        }
                                        dataOfDay['price'] = Schedule['price'];
                                        dataOfDay['promo'] = Schedule['promo'];
                                        dataOfDay['qty'] = Schedule['qty'];
                                    }

                                }
                            }
                        }
                    }
                    //check date in booking order
                    if(dataOfDay['qty'] != '')
                    {
                        if(bookingOrders)
                        {
                            var tempQty = parseInt(dataOfDay['qty']);
                            for(var key_order in bookingOrders)
                            {
                                if(bookingOrders[key_order]['check_in'] != 'undefined' && bookingOrders[key_order]['check_out'])
                                {
                                    //new object check in and check out in order
                                    var orderCheckIn = datesHelper.getDateByStringYearMonthDay(bookingOrders[key_order]['check_in']);
                                    var orderCheckOut = datesHelper.getDateByStringYearMonthDay(bookingOrders[key_order]['check_out']);
                                    //check day exit in item
                                    var checkDayOrder = false;
                                    if(bookingTime == '1')
                                    {
                                        if(orderCheckIn.getTime() <= dayObj.getTime() && dayObj.getTime() < orderCheckOut.getTime())
                                        {
                                            checkDayOrder = true;
                                        }
                                    }
                                    else
                                    {
                                        if(orderCheckIn.getTime() <= dayObj.getTime() && dayObj.getTime() <= orderCheckOut.getTime())
                                        {
                                            checkDayOrder = true;
                                        }
                                    }
                                    if(checkDayOrder)
                                    {
                                        if(tempQty > 0)
                                        {
                                            tempQty = tempQty - parseInt(bookingOrders[key_order]['qty']);
                                        }
                                        else
                                        {
                                            break;
                                        }
                                    }
                                }

                            }
                            availableQty = tempQty > 0 ? tempQty : 0;
                        }
                    }
                }
                var arrayDay = new Array();
                arrayDay.push('<div id="'+dayId+'" class="booking-item-day '+strClassDay+' '+dataOfDay['status_class']+'">');
                arrayDay.push('		<div class="booking-item-day-left">');
                arrayDay.push('			<div class="day-header"></div>');
                arrayDay.push('			<div class="day-content"></div>');
                arrayDay.push('		</div>'); // end left
                arrayDay.push('		<div class="booking-item-day-content">');
                arrayDay.push('			<div class="day-header">'+day+'</div>');
                arrayDay.push('			<div class="day-content">');
                if(dataOfDay['status_text'] != '')
                {
                    arrayDay.push('<div class="day-content-qty">'+dataOfDay['qty']+'</div>');
                    if(dataOfDay['status'] == 'available' || dataOfDay['status'] == 'special')
                    {
                        arrayDay.push('<div class="day-comtent-text"> ('+availableQty+')'+dataOfDay['status_text']+'</div>');
                        if(dataOfDay['price'] != null)
                        {
                            if(dataOfDay['promo'] != null)
                            {
                                arrayDay.push('<div class="day-comtent-price-underline">'+currency+dataOfDay['price']+'</div>');
                                arrayDay.push('<div class="day-comtent-promo">'+currency+dataOfDay['promo']+'</div>');
                            }
                            else
                            {
                                arrayDay.push('<div class="day-comtent-price">'+currency+dataOfDay['price']+'</div>');
                            }
                        }
                    }
                    else
                    {
                        arrayDay.push('<div class="day-comtent-text">'+dataOfDay['status_text']+'</div>');
                    }
                }
                arrayDay.push('			</div>'); //end day content
                arrayDay.push('		</div>'); // end booking-item-day-content
                arrayDay.push('		<div class="booking-item-day-right">');
                arrayDay.push('			<div class="day-header"></div>');
                arrayDay.push('			<div class="day-content"></div>');
                arrayDay.push('		</div>');
                arrayDay.push('</div>'); // end item day
                return arrayDay.join('');
            },
            defaultEvent : function()
            {
                $('#calendar-header-btn-add-month',contentCalendar).bind('click',function(){
                    numberMonth++;
                    methods.updateMonth(curMonth,curYear);
                });
                $('#calendar-header-btn-romove-month',contentCalendar).bind('click',function(){
                    if(numberMonth > 1)
                    {
                        numberMonth--;
                        methods.updateMonth(curMonth,curYear);
                    }
                });
                $('.item-one .calendar-header-btn-pre-one').off('click');
                $('.item-one .calendar-header-btn-pre-one').bind('click',function(){
                    //$('#calendar-header-btn-pre').bind('click',function(){
                    //if(curMonth > curDate.getMonth() + 1 && curYear == curDate.getFullYear())
                    if(curMonth > curDate.getMonth() + 1 && curYear == curDate.getFullYear())
                    {
                        curMonth--;
                        methods.updateMonth(curMonth,curYear);
                        $('.item-one .first-month').html(curMonth+' - '+curYear)
                    }
                    else if(curYear > curDate.getFullYear())
                    {
                        if(curMonth == 1)
                        {
                            curMonth = 12;
                            curYear--;
                        }
                        else{
                            curMonth--;
                        }
                        methods.updateMonth(curMonth,curYear);
                        $('.item-one .first-month').html(curMonth+' - '+curYear)
                    }
                });
                //$('#calendar-header-btn-next').bind('click',function(){
                $('.item-one .calendar-header-btn-next-one').off('click');
                $('.item-one .calendar-header-btn-next-one').bind('click',function(){
                    curMonth++;
                    if(curMonth > 12)
                    {
                        curMonth = 1;
                        curYear++;

                    }
                    methods.updateMonth(curMonth,curYear);
                    $('.item-one .first-month').html(curMonth+' - '+curYear);
                });
            },
            intEventDay : function()
            {
                return false;
                $('.booking-item-day',contentCalendar).unbind('click');
                $('.booking-item-day',contentCalendar).bind('click',function(){
                    var strDayId = $(this).attr('id');
                    var arDayId = strDayId.split('_');
                    var strDateId = arDayId[1];
                    var arDateIds = strDateId.split('-');
                    var dayObj = new Date(arDateIds[0],arDateIds[1],arDateIds[2]);
                    var currentDate = new Date(curDate.getFullYear(),curDate.getMonth() + 1,curDate.getDate());
                    if(dayObj.getTime() < currentDate.getTime())
                    {
                        return false;
                    }
                    if(dayClick == '')
                    {
                        dayClick = strDateId;
                        return;
                    }
                    else
                    {
                        var startDate = dayClick;
                        var endDate = strDateId;
                        var objStart = new Date(dayClick);
                        var objEnd = new Date(strDateId);
                        if(objEnd.getTime() < objStart.getTime())
                        {
                            startDate = strDateId;
                            endDate = dayClick;
                        }
                        var arStartDate = startDate.split('-');
                        var startDate1 =  arStartDate[2]+'/'+arStartDate[1]+'/'+arStartDate[0];
                        var arEndDate = endDate.split('-');
                        var endDate1 =  arEndDate[2]+'/'+arEndDate[1]+'/'+arEndDate[0];
                        dayClick = '';
                        if($.trim(startDate) != '' && $.trim(endDate) != '')
                        {
                            $('.bk-loading-mask').css('display','block');
                            $('#add-new-calendar').css('display','none');
                            $.ajax({
                                url: urlAddItem,
                                dataType : 'json',
                                type: 'POST',
                                data: {calendar_id:0,booking_id : bookingId,booking_type: bookingType,check_in : startDate, check_out : endDate},
                                success : function(res)
                                {
                                    $('#form-booking-calendar').html(res.html_calendar_form);
                                    $('.bk-loading-mask').css('display','none');
                                },
                                error : function()
                                {

                                }
                            });
                        }
                    }
                });
                $('.booking-item-day').hover(
                    function() {
                        var strDayId = $(this).attr('id');
                        var arDayId = strDayId.split('_');
                        var strDateId = arDayId[1];
                        if(dayClick != '')
                        {
                            $('.day-selected').removeClass('day-selected');
                            methods.selectedDays(dayClick,strDateId);
                        }
                    }, function() {
                    }
                );
            },
            updateMonth : function(month,year)
            {
                $('.booking-calendar-content-one').html('');
                var mYear = year;
                var monthClass = '';
                for(var i = month; i < (numberMonth + month); i++)
                {
                    mMonth = i;
                    if(i > 12)
                    {
                        mMonth = i % 12;
                    }
                    if(i == numberMonth + month - 1)
                    {
                        monthClass = 'last-month';
                    }
                    if(mMonth == 1 && i != 1)
                    {
                        mYear++;
                    }
                    methods.intMonths(mYear,mMonth,monthClass);
                }
                methods.rsDay();
            },
            //function helper
            checkDayBetween: function(day,day1,day2) //date format is Y-m-d
            {
                var date1 = new Date(day1);
                var date2 = new Date(day2);
                var date3 = new Date(day);
                var intDate1 = date1.getTime();
                var intDate2 = date2.getTime();
                var intDate3 = date3.getTime();
                if(intDate3 < intDate1 || intDate3 > intDate2)
                    return false;
                return true;
            },
            getSizeObjct: function(obj)
            {
                var i = 0;
                for(var key in obj)
                {
                    i++;
                }
                return i;
            },
            selectedDays: function (date1,date2)
            {
                var date1 = new Date(date1);
                var date2 = new Date(date2);
                var timeDiff = Math.abs(date2.getTime() - date1.getTime());
                var oneDay = 1000 * 3600 * 24;
                var day1 = date1.getTime();
                var day2 = date2.getTime();
                if(date1.getTime() > date2.getTime())
                {
                    day1 = date2.getTime();
                    day2 = date1.getTime();
                }
                while(day1 <= day2)
                {
                    var objDate = new Date(day1);
                    var month = objDate.getMonth() + 1;
                    month = month >= 10 ? month : '0'+month;
                    strDay = objDate.getDate() >= 10 ? objDate.getDate() : '0'+objDate.getDate();
                    var strDate = objDate.getFullYear()+'-'+month+'-'+strDay;
                    if($('#0_'+strDate).length)
                    {
                        $('#0_'+strDate).addClass('day-selected');
                    }
                    if($('#2_'+strDate).length)
                    {
                        $('#2_'+strDate).addClass('day-selected');
                    }
                    if($('#1_'+strDate).length)
                    {
                        $('#1_'+strDate).addClass('day-selected');
                    }
                    day1 += oneDay;
                }
            }

        }
        return methods.init();
    }
})