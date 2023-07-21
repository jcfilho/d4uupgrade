define([
    'jquery'
],function($){

    return {
        selectors : {
            paymetGroup             : '.opc-payment .payment-group',
            paymetGroupTitle        : '.opc-payment .payment-group .payment-method-title',
            paymetGroupTitleLabel   : '.opc-payment .payment-group .payment-method-title .label',
            billingForm             : '.billing-address-form form',
            fieldsetForm            : '.checkout-billing-address fieldset.fieldset:first',
            billingFormBtnSubmit    : '.checkout-billing-address .actions-toolbar .primary .action',
            billingFormErrorField   : '#billing-new-address-form .field._error',
            billingDetails          : '.checkout-billing-address .billing-address-details'
        },
        removeAttrForToPaymentMethods: function(){
            var self = this;
            $(self.selectors.paymetGroupTitleLabel).removeAttr('for');
            self.changeFuncionality();
        },
        scrollToForm : function(){
            var self = this;
            $('html, body').animate({ scrollTop: $(self.selectors.billingForm).offset().top}, 700);
        },
        changeFuncionality: function () {
            var self = this;
            $(self.selectors.paymetGroupTitleLabel).off('click').css({
                cursor: 'pointer'
            });
            $(self.selectors.paymetGroupTitleLabel).on('click',function(){
                var labelElement = this;
                if( $(self.selectors.fieldsetForm).is(':visible')){
                    if( $(self.selectors.billingFormErrorField).length > 0 ){
                        self.scrollToForm();
                        return false;
                    }
                    $(self.selectors.billingFormBtnSubmit).trigger('click');
                    self.scrollToForm();

                }else{
                    if( $(self.selectors.billingDetails).html() != '' ){
                        $(labelElement).prev('input.radio').trigger('click');
                    }
                }
            });
        }
    };

});