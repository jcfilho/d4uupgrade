define(
    [
        'jquery',
        'Magento_Customer/js/action/login',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/url'
    ],function($, loginAction, fullScreenLoader,urlBuilder){
        'use strict';
        
        return function (Component) {
            return Component.extend({
                /**
                 * Log in form submitting callback.
                 *
                 * @param {HTMLElement} loginForm - form element.
                 */
                login: function (loginForm) {
                    var loginData = {},
                        formDataArray = $(loginForm).serializeArray();

                    formDataArray.forEach(function (entry) {
                        loginData[entry.name] = entry.value;
                    });

                    if (this.isPasswordVisible() && $(loginForm).validation() && $(loginForm).validation('isValid')) {
                        fullScreenLoader.startLoader();
                        var urlRedirect = urlBuilder.build('checkout');
                        loginAction(loginData,urlRedirect).always(function () {
                            fullScreenLoader.stopLoader();
                        });
                    }
                },
                openPopupLogin: function(){
                    $('.popup-with-forgot').magnificPopup('open');
                }
            });
        }
    });