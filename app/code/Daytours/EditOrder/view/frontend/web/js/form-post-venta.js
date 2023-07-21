define(
    [
        'jquery',
        'Magento_Ui/js/modal/confirm',
        'mage/translate',
        'bk_calendar_rage',
        'moment'
    ],
    function($,confirmation,$tr,bkCalendarRange,moment){

        var appFormPostPurchase = function (){};

        appFormPostPurchase.prototype = {

            variables: {
                calendarTranslate: '',
                optionsToCalendar : ''
            },
            selectors: {
                mainContent: '#maincontent',
                formCompleteOrder: '#form-complete-order',
                btnSave : '.control-submit-form .save',
                boxCalendarBirthday: '.calendar-date-post-purchase',
                boxCalendarArrivalDate: '.arrival-date',
                boxCalendarDepartingTime: '.departing-date',
            },

            init: function(){
                var self = this;

                //Prepare values
                self.initTranslateCalendar();

                //Calendars
                self.calendarToBirthday();
                self.calendarArrivalDate();
                self.calendarDepartingDate();

                //action to save data
                self.clickToSave();
            },

            initTranslateCalendar: function(){
                var self = this;
                self.variables.calendarTranslate = new magebayTextJqueryCalendar();
            },

            buildBasicOptionsToCalendar: function(){
                var self = this;
                return {
                    singleDatePicker: true,
                    ranges : {},
                    showDropdowns: true,
                    // minDate: '01/01/1925',
                    // maxDate : moment().endOf('day'),
                    // endDate : moment().endOf('day'),
                    autoUpdateInput : false,
                    locale: {
                        format: 'DD/MM/YYYY',
                        firstDay: parseInt(0),
                        daysOfWeek : self.variables.calendarTranslate.getdayNamesMin(),
                        monthNames: self.variables.calendarTranslate.getShortMonth(),
                        applyLabel : $tr('Apply'),
                        cancelLabel: $tr('Cancel')
                    }
                }
            },

            calendarToBirthday: function(){
                var self = this;
                if( $(self.selectors.boxCalendarBirthday).length ){

                    var optionsCalendar = self.buildBasicOptionsToCalendar();
                    optionsCalendar.minDate = '01/01/1925';
                    optionsCalendar.maxDate = moment().endOf('day');
                    optionsCalendar.endDate = moment().endOf('day');

                    $(self.selectors.boxCalendarBirthday).daterangepicker(optionsCalendar);

                    $(self.selectors.boxCalendarBirthday).on('apply.daterangepicker', function(ev, picker) {
                        var dateForInput = picker.startDate.format('DD/MM/YYYY');
                        $(this).val(dateForInput);
                    });
                }
            },

            calendarArrivalDate: function(){
                var self = this;
                if( $(self.selectors.boxCalendarArrivalDate).length ){

                    var optionsCalendar = self.buildBasicOptionsToCalendar();
                    optionsCalendar.minDate = moment().format('DD/MM/YYYY');

                    $(self.selectors.boxCalendarArrivalDate).daterangepicker(optionsCalendar);

                    $(self.selectors.boxCalendarArrivalDate).on('apply.daterangepicker', function(ev, picker) {
                        var dateForInput = picker.startDate.format('DD/MM/YYYY');
                        $(this).val(dateForInput);
                    });

                    $(self.selectors.boxCalendarArrivalDate).on('apply.daterangepicker', function(ev, picker) {
                        var dateDepartingCalendar   = picker.startDate.format('DD/MM/YYYY');
                        var departingSelector = $(this).data('dependent');
                        var localeDaterangePicker   = $("input[name='"+departingSelector+"']").data('daterangepicker').locale.format;
                        $("input[name='"+departingSelector+"']").data('daterangepicker').minDate = moment(dateDepartingCalendar,localeDaterangePicker);
                        $("input[name='"+departingSelector+"']").val('');
                    });

                }
            },

            calendarDepartingDate: function(){
                var self = this;
                if( $(self.selectors.boxCalendarDepartingTime).length ){

                    var optionsCalendar = self.buildBasicOptionsToCalendar();
                    optionsCalendar.minDate = moment().format('DD/MM/YYYY');

                    $(self.selectors.boxCalendarDepartingTime).daterangepicker(optionsCalendar);

                    $(self.selectors.boxCalendarDepartingTime).on('apply.daterangepicker', function(ev, picker) {
                        var dateForInput = picker.startDate.format('DD/MM/YYYY');
                        $(this).val(dateForInput);
                    });
                }
            },

            clickToSave: function(){
                var self = this;
                $(self.selectors.mainContent).on('click',self.selectors.btnSave,function(){
                    if( $(self.selectors.formCompleteOrder).validation('isValid') ){
                        confirmation({
                            title: $.mage.__('Confirmation'),
                            content: $.mage.__('The information given in this form is true, complete and accurate and corresponds to my itinerary and/or travel documents.'),
                            buttons: [{
                                text: $.mage.__('Cancel'),
                                class: 'action-secondary action-dismiss',

                                /**
                                 * Click handler.
                                 */
                                click: function (event) {
                                    this.closeModal(event);
                                }
                            },{
                                text: $.mage.__('Send'),
                                class: 'action-primary action-accept',

                                /**
                                 * Click handler.
                                 */
                                click: function (event) {
                                    this.closeModal(event, true);
                                }
                            }],
                            actions: {
                                confirm: function(){
                                    $(self.selectors.formCompleteOrder)[0].submit();
                                },
                                cancel: function(){},
                                always: function(){}
                            }
                        });
                    }else{

                    }
                });
            }

        };

        $(document).ready(function(){
            var appPostP = new appFormPostPurchase();
            appPostP.init();
        });
    }
);