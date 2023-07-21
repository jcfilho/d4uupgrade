define(
    [
        'jquery',
        'ko',
        'uiComponent'
    ],
    function(
        $,
        ko,
        Component
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Daytours_Checkout/after_payments'
            },
            isVisible: function () {
                return true;
            },
            initialize: function () {
                var self = this;
                this._super();
                $(function() {
                    $('body').on("click", '#place-order-trigger', function () {
                        $(".payment-method._active").find('.action.primary.checkout').trigger( 'click' );
                        //console.log('It works');
                    });
                });
            }

        });
    }
);