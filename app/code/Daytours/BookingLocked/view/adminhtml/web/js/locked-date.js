define(
    [
      'jquery',
      'Magento_Ui/js/modal/alert',
      'Magento_Ui/js/modal/confirm'
    ],
    function($, alert, confirm){

      let app = function () {};
      app.prototype = {

        config                : {},
        selectors:  {
          btnAddNewLockedDate   : '#block-date',
          btnAddNewDate         : '#add-new-calendar',
          btnDeleteLocked       : 'body .items-locked .item-locked .remove',
          btnSaveNewLockedDate  : 'body #save-new-locked-date',
          containerRight        : '#form-booking-calendar',
          containerCalendar     : '.booking-popup-content .item-one #booking-calendar',
          inputDateToBlock      : 'body #item-day-locked',
          /* ---- calendar two --- */
          btnAddNewLockedDateC2   : '#block-date-calendar-two',
          btnAddNewDateC2         : '#add-new-calendar-two',
          btnDeleteLockedC2       : 'body .items-locked .item-locked .remove',
          btnSaveNewLockedDateC2  : 'body #save-new-locked-date',
          containerRightC2        : '#form-booking-calendar-two',
          containerCalendarC2     : '.booking-popup-content .item-two #booking-calendar-two',
          inputDateToBlockC2      : 'body #item-day-locked',

        },

        init: function (config) {

          let self = this;
          self.config = config;
          /*
          config : {
            urlToLoadLockedDates, urlToSaveLockedDates ,urlToDeleteLockedDates, productId, currentDate, formatDate
          } from app/code/Daytours/Bookingsystem/Block/Adminhtml/Calendars.php
            method : getDataToLockedDates()
           */

          self._showFormToAddDates();

        },

        _enableDatepickerAndSaveNew:function(){
          let self = this;
          self._enableDatepicker();
          self._enableSaveNewLockedDate();
        },

        _activeNewCalendar:function(){
          let self = this;
          $(self.selectors.btnAddNewDate).css({display: 'block'});
        },

        _loadListLockedDates: function(){
          let self = this;
          $.ajax({
            showLoader: true,
            url: self.config.urlToLoadLockedDates,
            data: {
              productId : self.config.productId
            },
            type: "GET",
            dataType: 'json'
          }).done(function(data){
            $(self.selectors.containerRight).html(data.result_html);
            self._enableDatepickerAndSaveNew();
            self._enableListToDelete();

          }).error(function(data){
            console.log('Error loading loacked dates');
            console.log(data);
          });

        },

        _showFormToAddDates : function () {

          let self = this;
          $(self.selectors.btnAddNewLockedDate).on('click',function(){
            self._activeNewCalendar();
            $(self.selectors.containerRight).html('');
            self._loadListLockedDates();
           });

        },

        _enableDatepicker:function () {
          let self = this;

          $(self.selectors.inputDateToBlock).off();
          $(self.selectors.inputDateToBlock).datepicker(
            {
              minDate : new Date(self.config.currentDate),
              dateFormat: self.config.formatDate,
              changeMonth : true,
              onSelect: function( selectedDate, inst)
              {
                let date = $.datepicker.parseDate(inst.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, inst.settings);
                let dateDBFormat = jQuery.datepicker.formatDate("yy-mm-dd", date, inst.settings);
                $(self.selectors.inputDateToBlock).data('formatDB',dateDBFormat);
              }
            });

        },

        _enableSaveNewLockedDate: function(){
          let self = this;
          $(self.selectors.btnSaveNewLockedDate).off();
          $(self.selectors.btnSaveNewLockedDate).on('click', function () {
            let dateSelected = $(self.selectors.inputDateToBlock).data('formatDB');
            if (dateSelected && dateSelected !== '') {
              self._saveNewLockedDate(dateSelected);
            }
          });
        },

        _enableListToDelete: function(){
          let self = this;
          $(self.selectors.btnDeleteLocked).off();
          $(self.selectors.btnDeleteLocked).on('click',function(){
            let lockedId = $(this).data('id');
            confirm({
              content: $.mage.__('Are you sure you want to delete this item.?'),
              actions: {
                /**
                 * Confirm.
                 */
                confirm: function () {
                  return $.ajax({
                    showLoader: true,
                    url: self.config.urlToDeleteLockedDates,
                    data: {
                      lockedId : lockedId
                    },
                    type: "POST",
                    dataType: 'json'
                  }).done(function(data){
                    if( data.result ){
                      self._showAlert('Removed', data.message);
                      self._loadListLockedDates();
                      self._reloadCalendar();
                    }else{
                      self._showAlert('Error', data.message);
                    }
                  }).error(function(data){
                    console.log('Error loading locked dates');
                    console.log(data);
                  });
                },

                /**
                 * @return {Boolean}
                 */
                cancel: function () {
                  return false;
                }
              }
            });
          });
        },

        _saveNewLockedDate: function(date){
          let self = this;
          $.ajax({
            showLoader: true,
            url: self.config.urlToSaveLockedDates,
            data: {
              productId : self.config.productId,
              date : date
            },
            type: "POST",
            dataType: 'json'
          }).done(function(data){

            if( data.result ){
              self._showAlert('Saved', data.message);
              self._loadListLockedDates();
              self._reloadCalendar();
            }else{
              self._showAlert('Error', data.message);
            }

          }).error(function(data){
            console.log('Error loading locked dates');
            console.log(data);
          });
        },

        _showAlert: function(title,message){
          let self = this;
          alert({
            title: title,
            content: message
          });
        },

        _reloadCalendar: function(){
          let self = this;
          let objData = $(self.selectors.containerCalendar).data('objData');
          $(self.selectors.containerCalendar).MagebayAnyBooking(objData);
        }

      };

      return function (config) {

        let appLockedDate = new app();
        appLockedDate.init(config);

      };


});
