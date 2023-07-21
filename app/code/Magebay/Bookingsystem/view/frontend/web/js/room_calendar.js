define([
    'jquery'
], function($){
	$.fn.MagebayAnyBookingHotel = function(objData)
	{
		var Data = {
			'data_url': 'load-calendar.php',
			'booking_id': 0,
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
			'pre_text' : 'Prev',
			'format_date' : 'dd/mm/yy',
			'url_booking' : 'booking.php',
			'str_current_date' : '',
			'disable_day' : '',
            'first_day' : 1
		}
		Schedules = {};
		bookingOrders = {};
		FirstDay = 1;
		contentCalendar = this;
		curDate = new Date();
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
		booking_type = '1';
		addText = 'Add';
		removeText = 'Remove';
		nextText = 'Next';
		preText = 'Prev';
		formatDate = 'dd/mm/yy';
		urlBooking = 'booking.php';
		bookingTime = '1'; 
		arDisableDays = [];
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
				DataURL = Data['data_url'];
				bookingId = Data['booking_id'];
				bookingType = Data['booking_type'];
				currency = Data['currency'];
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
				formatDate = Data['format_date'];
				objStatusText = Data['obj_status_text'];
                FirstDay = Data['first_day'];
				if(Data['str_current_date'] != '')
				{
					curDateText = Data['str_current_date'];
					curDate = new Date(Data['str_current_date']);
				}
				if(Data['disable_day'] != '')
				{
					var tempDisableDay = Data['disable_day'];
					arDisableDays = tempDisableDay.split(',');
				}
				methods.parseCalendarData();
			},
			parseCalendarData : function()
			{
				//get data from database
				$('#booking-loader').css('display','block');
				$.post(DataURL, {booking_id:bookingId,booking_type: bookingType}, function(data)
				{
					if (data){
						Schedules = data['data_calendar'];
						bookingOrders = data['data_order'];
					}
					methods.initCalendar();
					var checkIn = $('#room-check-in').val();
					var checkOut = $('#room-check-out').val();
					if($.trim(checkIn) != '' || $.trim(checkOut) != '')
					{
						$('.booking-room-results').html('');
						$.ajax({
							url : urlBooking,
							dataType : 'json',
							type : 'POST',
							data : $('#product_addtocart_form').serialize(),
							success : function(res)
							{
								$('.booking-room-results').html(res.booking_result);
								var tempCheckIn = $('#temp-check-in').val();
								var tempCheckOut = $('#temp-check-out').val();
								methods.selectedDays(tempCheckIn,tempCheckOut);
								$('#booking-loader').css('display','none');
							},
							error : function()
							{
								$('#booking-loader').css('display','none');
							}
						});
					}
					else
					{
						$('#booking-loader').css('display','none');
					}
				});
			},
			initCalendar : function()
			{
				var arrayContent = new Array();
				arrayContent.push('<div class="magebay-booking-calendar">');
				arrayContent.push('		<div class="booking-calendar-header">');
				arrayContent.push('			<div class="calendar-header-btn">');
				arrayContent.push('				<div id="calendar-header-left">');
				arrayContent.push('					<div id="calendar-header-btn-add-month" title="'+addText+'">'+addText+'</div>');
				arrayContent.push('					<div id="calendar-header-btn-romove-month" title="'+removeText+'">'+removeText+'</div>');
				arrayContent.push('				</div>');
				arrayContent.push('				<div id="calendar-header-center">');
				arrayContent.push('					<div id="calendar-header-btn-text">'+bookingLabel)
				arrayContent.push('						<div class="first-month">'+curMonth+' - '+curYear+'</div>');
				arrayContent.push('					</div>');
				arrayContent.push('				</div>');
				arrayContent.push('				<div id="calendar-header-right">');
				arrayContent.push('					<div id="calendar-header-btn-next" title="'+nextText+'">'+nextText+'</div>');
				arrayContent.push('					<div id="calendar-header-btn-pre" title="'+preText+'">'+preText+'</div>');
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
				arrayContent.push('		<div class="booking-calendar-content">');
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
                var no = FirstDay - 1;
				$('.calendar-header-day',contentCalendar).each(function(){
                    if(no >= 7)
                    {
                        no = no - 7;
                    }
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
				var noDays = new Date(year,month,0).getDate();
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
					var tempPrveMonth = month -1;
					var tempPrveYear = year;
					if(month != curMonth)
					{
						classDay = 'booking-day-none';
					}
					else
					{
						classDay = 'booking-day-past';
					}
					if(month == 1)
					{
						tempPrveMonth = 12;
						tempPrveYear = year - 1;
					}
					var dayHhtml = methods.intDays(0,tempPrveYear,tempPrveMonth ,noDaysPerivous - i,classDay);
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
					var tempNextMonth = month + 1;
					var tempNextYear = year;
					if(month == curMonth + numberMonth - 1)
					{
						classDay = 'booking-day-next-month';
					}
					else
					{
						classDay = 'booking-day-none';
					}
					if(month == 12)
					{
						tempNextMonth = 1;
						tempNextYear = year + 1;
					}
					var dayHhtml = methods.intDays(2,tempNextYear,tempNextMonth,k,classDay);
					arrayMonth.push(dayHhtml);
				}
				arrayMonth.push('<div class="clear"></div>');
				arrayMonth.push('</div>');
				$('.booking-calendar-content',contentCalendar).append(arrayMonth.join(''));
				methods.intEventDay();
			},
			intDays : function(day_id,year,month,day,classDay)
			{
				var strClassDay = classDay;
				//var dayObj = new Date(year,month,day);
				//var currentDate = new Date(curDate.getFullYear(),curDate.getMonth() + 1,curDate.getDate());
				var strDay = day >= 10 ? day : '0'+day;
				var strMonth = month >= 10 ? month : '0'+month;
				var dayObj = new Date(year+'-'+strMonth+'-'+strDay);
				var dayId = day_id+'_'+year+'-'+strMonth+'-'+strDay;
				var key_day_id = year+'-'+strMonth+'-'+strDay;
				var price = "";
				var promo = "";
				var qty = "";
				var dataOfDay = {
							'status' :'',
							'price' : '',
							'promo' : '',
							'text_price' : '',
							'text_promo' : '',
							'qty' : '',
							'status_text' : '',
							'status_class' : ''
						};
				var strSatus = ""
				var dateTextMonth = dayObj.getMonth() + 1;
				dateTextMonth = dateTextMonth < 10 ? '0'+dateTextMonth : dateTextMonth;
				dateTextDay = dayObj.getDate() < 10 ? '0'+dayObj.getDate() : dayObj.getDate();
				var dateText = dayObj.getFullYear()+'-'+dateTextMonth+'-'+dateTextDay;
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
								var objDayStart = new Date(arDayStart[0]+'-'+arDayStart[1]+'-'+arDayStart[2]);
								var objEndDate = new Date(arDayEnd[0]+'-'+arDayEnd[1]+'-'+arDayEnd[2]);
								// not data of past date current
								if(dayObj.getTime() < objDayStart.getTime() || dayObj.getTime() > objEndDate.getTime())
								{
									continue;
								}
								if(dayObj.getTime() >= objDayStart.getTime() && dayObj.getTime() <= objEndDate.getTime())
								{
									dataOfDay['status'] = Schedule['status'];
									dataOfDay['status_class'] = 'day-'+Schedule['status'];
									if(Schedule['status'] == 'block' || Schedule['status'] == 'unavailable')
									{
										if(Schedule['status'] == 'block')
										{
											if(Schedule['group_day'] == '1')
											{
												if(strDayStart == key_day_id || (strDayStart != key_day_id && key_day_id == curDateText))
												{
													dataOfDay['status_text'] = objStatusText[Schedule['status']];
													//not day finally
													if(curDateText != strDayEnd)
													{
														dataOfDay['status_class'] += ' '+Schedule['status']+'-group-day-first';
													}
												}
												else if(strDayEnd == key_day_id)
												{
													dataOfDay['status_class'] += ' '+Schedule['status']+'-group-day-last';
												}
												else
												{
													dataOfDay['status_class'] += ' '+Schedule['status']+'-group-day';
												}
											}
											else
											{
												dataOfDay['status_text'] = objStatusText[Schedule['status']];
											}
										}
										else
										{
											dataOfDay['status_text'] = objStatusText[Schedule['status']];
										}
									}
									else if(Schedule['status'] == 'special' || Schedule['status'] == 'available')
									{
										if(Schedule['group_day'] == '1')
										{
											if(strDayStart == key_day_id || (strDayStart != key_day_id && key_day_id == curDateText))
											{
												dataOfDay['status_text'] = objStatusText[Schedule['status']];
												dataOfDay['price'] = Schedule['price'];
												dataOfDay['promo'] = Schedule['promo'];
												dataOfDay['text_price'] = Schedule['text_price'];
												dataOfDay['text_promo'] = Schedule['text_promo'];
												dataOfDay['qty'] = Schedule['qty'];
												if(curDateText != strDayEnd)
												{
													dataOfDay['status_class'] += ' '+Schedule['status']+'-group-day-first';
												}
											}
											else if(strDayEnd == key_day_id)
											{
												dataOfDay['status_class'] += ' '+Schedule['status']+'-group-day-last';
											}
											else
											{
												dataOfDay['status_class'] += ' '+Schedule['status']+'-group-day';
											}
										}
										else
										{
											if($.inArray(''+dayObj.getDay(), arDisableDays) != -1)
											{
												dataOfDay['status'] = 'unavailable';
												dataOfDay['status_class'] = 'day-unavailable';
												dataOfDay['status_text'] = objStatusText[dataOfDay['status']];
											}
											else
											{
												dataOfDay['status_text'] = objStatusText[Schedule['status']];
												dataOfDay['price'] = Schedule['price'];
												dataOfDay['promo'] = Schedule['promo'];
												dataOfDay['text_price'] = Schedule['text_price'];
												dataOfDay['text_promo'] = Schedule['text_promo'];
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
										dataOfDay['price'] = Schedule['price'];
										dataOfDay['promo'] = Schedule['promo'];
										dataOfDay['text_price'] = Schedule['text_price'];
										dataOfDay['text_promo'] = Schedule['text_promo'];
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
									var orderCheckIn = new Date(bookingOrders[key_order]['check_in']);
									var orderCheckOut = new Date(bookingOrders[key_order]['check_out']);
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
										//console.log(tempQty);
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
							if(tempQty > 0)
							{
								dataOfDay['qty'] = tempQty;
							}
							else
							{
								dataOfDay['status'] = 'block';
								dataOfDay['qty'] = '';
								dataOfDay['status_text'] = objStatusText['block'];
								dataOfDay['status_class'] = 'day-block';
								dataOfDay['price'] = '';
								dataOfDay['promo'] = '';
							}
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
												arrayDay.push('<div class="day-comtent-text">'+dataOfDay['status_text']+'</div>');
												if(dataOfDay['status'] == 'available' || dataOfDay['status'] == 'special')
												{
													if(dataOfDay['price'] != null)
													{
														if(dataOfDay['promo'] != null)
														{	
															arrayDay.push('<div class="day-comtent-price-underline">'+currency+dataOfDay['text_price']+'</div>');
															arrayDay.push('<div class="day-comtent-promo">'+currency+dataOfDay['text_promo']+'</div>');
														}
														else
														{
															arrayDay.push('<div class="day-comtent-price">'+currency+dataOfDay['text_price']+'</div>');
														}
													}
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
				$('#calendar-header-btn-pre').bind('click',function(){
					if(curMonth > curDate.getMonth() + 1 && curYear == curDate.getFullYear())
					{
						curMonth--;
						methods.updateMonth(curMonth,curYear);
						$('.first-month').html(curMonth+' - '+curYear)
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
						$('.first-month').html(curMonth+' - '+curYear)
					}
				});
				$('#calendar-header-btn-next').bind('click',function(){
					curMonth++;
					if(curMonth > 12)
					{
						curMonth = 1;
						curYear++;
						
					}
					methods.updateMonth(curMonth,curYear);
					$('.first-month').html(curMonth+' - '+curYear);
				});
			},
			intEventDay : function()
			{
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
						$('#temp-check-in').val(startDate);
						$('#temp-check-out').val(endDate);
						var arStartDate = startDate.split('-');
						var arEndDate = endDate.split('-');
						var startDate1 = '';
						var endDate1 = '';
						if(formatDate == 'dd/mm/yy')
						{
							startDate1 =  arStartDate[2]+'/'+arStartDate[1]+'/'+arStartDate[0];
							endDate1 =  arEndDate[2]+'/'+arEndDate[1]+'/'+arEndDate[0];
						}
						else if(formatDate == 'mm/dd/yy')
						{
							startDate1 =  arStartDate[1]+'/'+arStartDate[2]+'/'+arStartDate[0];
							endDate1 =  arEndDate[1]+'/'+arEndDate[2]+'/'+arEndDate[0];
						}
						else if(formatDate == 'yy/dd/mm')
						{
							startDate1 =  arStartDate[0]+'/'+arStartDate[2]+'/'+arStartDate[1];
							endDate1 =  arEndDate[0]+'/'+arEndDate[2]+'/'+arEndDate[1];
						}
						else if(formatDate == 'yy/mm/dd')
						{
							startDate1 =  arStartDate[0]+'/'+arStartDate[1]+'/'+arStartDate[2];
							endDate1 =  arEndDate[0]+'/'+arEndDate[1]+'/'+arEndDate[2];
						}
						else if(formatDate == 'dd-mm-yy')
						{
							startDate1 =  arStartDate[2]+'-'+arStartDate[1]+'-'+arStartDate[0];
							endDate1 =  arEndDate[2]+'-'+arEndDate[1]+'-'+arEndDate[0];
						}
						else if(formatDate == 'mm-dd-yy')
						{
							startDate1 =  arStartDate[1]+'-'+arStartDate[2]+'-'+arStartDate[0];
							endDate1 =  arEndDate[1]+'-'+arEndDate[2]+'-'+arEndDate[0];
						}
						else if(formatDate == 'yy-dd-mm')
						{
							startDate1 =  arStartDate[0]+'-'+arStartDate[2]+'-'+arStartDate[1];
							endDate1 =  arEndDate[0]+'-'+arEndDate[2]+'-'+arEndDate[1];
						}
						else if(formatDate == 'yy/mm/dd')
						{
							startDate1 =  arStartDate[0]+'-'+arStartDate[1]+'-'+arStartDate[2];
							endDate1 =  arEndDate[0]+'-'+arEndDate[1]+'-'+arEndDate[2];
						}
						$('#room-check-in').val(startDate1);
						$('#room-check-out').val(endDate1);
                        $('#bk-room-checkin-checkout').val(startDate1 +' - '+ endDate1);
						dayClick = '';
						if($.trim(startDate1) != '' && $.trim(endDate1) != '')
						{
							$('.booking-room-results').html('');
							$('.magebay-overlay,.magebay-progress').css('display','block');
							$.ajax({
								url : urlBooking,
								dataType : 'json',
								type : 'POST',
								data : $('#product_addtocart_form').serialize(),
								success : function(res)
								{
									$('.booking-room-results').html(res.booking_result);
									$('.magebay-overlay,.magebay-progress').css('display','none');
									var tempCheckIn = $('#temp-check-in').val();
									var tempCheckOut = $('#temp-check-out').val();
									methods.selectedDays(tempCheckIn,tempCheckOut);
								},
								error : function()
								{
									
								}
							});
						}
					}
				});
				/* $('.booking-item-day',contentCalendar).unbind('hover');
				$('.booking-item-day',contentCalendar).bind('hover',function(){
					var strDayId = $(this).attr('id');
					var arDayId = strDayId.split('_'); 
					var strDateId = arDayId[1];
					if(dayClick != '')
					{
						$('.day-selected').removeClass('day-selected');
						methods.selectedDays(dayClick,strDateId);
					}
				}) */
				$( ".booking-item-day" ).hover(
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
				$('.booking-calendar-content',contentCalendar).html('');
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