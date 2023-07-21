define([
    'jquery',
    'mage/validation'
], function ($) {

    let app = function(){};

    app.prototype ={

        selectors : {
            msgSuccess : '.success-newsletter-ajax',
            msgError: '.error-newsletter-ajax',
            formId : '#newsletter-validate-detail-ajax',
            elementEmailForm : '#email-newsletter-ajax',
            btnClosePopup: '.content-popup-newsletter .no-thanks',
            elementsToHideAfterSubscription: '.content-popup-newsletter .p-title, .content-popup-newsletter .p-subtitle, .content-popup-newsletter .input-text-nll, .content-popup-newsletter .content-button, .content-popup-newsletter .no-thanks',
            popupWrapper: '.mfp-wrap.popup-newsletter-ajax .popup_wrapper'
        },

        config : null,

        init: function(config){
            let self = this;
            self.config = config;
            self.cleanForm();
            self.eventFormSubmit();
            self.closePopup();
        },
        cleanForm: function(){
            let self = this;
            $(self.selectors.msgSuccess,self.selectors.msgError).html('').css({display: 'none'});
        },
        eventFormSubmit: function(){
            let self = this;
            $(document).on('submit',self.selectors.formId,function(event) {

                event.preventDefault();

                self.cleanForm();

                let dataForm = $('#newsletter-validate-detail-ajax');

                if(dataForm.validation('isValid')){
                    // let param = 'email=' + $.trim($(self.selectors.elementEmailForm).val());
                    $.ajax({
                        showLoader: true,
                        url: self.config.ajaxUrl,
                        data: $(self.selectors.formId).serialize(),
                        type: "GET",
                        dataType: 'json'
                    }).done(function (data) {
                        if(data.status == "OK"){
                            $(self.selectors.msgSuccess).html(data.msg);
                            $(self.selectors.msgSuccess).css({display: 'block'});
                            $(self.selectors.msgError).html('');
                            $(self.selectors.msgError).css({display: 'none'});
                            $(self.selectors.elementsToHideAfterSubscription).css({display: 'none'});
                            $(self.selectors.popupWrapper).addClass('success-subscription');
                            setTimeout(function(){
                                $.magnificPopup.close();
                            },3000);
                        }else{
                            $(self.selectors.msgError).html(data.msg);
                            $(self.selectors.msgError).css({display: 'block'});
                            $(self.selectors.msgSuccess).html('');
                            $(self.selectors.msgSuccess).css({display: 'none'});
                        }

                    });
                }
                return false;
            });
        },

        closePopup: function(){
            let self = this;
            $(document).on('click',self.selectors.btnClosePopup,function(event) {
                event.preventDefault();
                $.magnificPopup.close();
            });
        }
    };

    function main(config, element) {

        var appNewsletterAjax = new app();
        appNewsletterAjax.init(config);

    }
    return main;
});