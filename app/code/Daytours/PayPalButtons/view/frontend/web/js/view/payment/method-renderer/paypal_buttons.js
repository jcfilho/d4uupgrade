/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'ko',
        'Magento_Ui/js/modal/alert',
        'jquery',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Daytours_ErrorLogs/js/model/errorLogs'
    ],
    function (Component, ko, alert, $, additionalValidators, quote, errorLogs) {
        'use strict';

        const getCustomerDataAsString = () => {
            try {
                const { firstname, lastname, telephone, countryId, region } = quote.billingAddress?._latestValue;
                const email = quote.guestEmail;
                const { base_currency_code } = quote.totals();
                const orderItems = window.checkoutConfig.quoteItemData.map(({ name, price, qty, sku, options }) => {
                    return {
                        "name": name,
                        "unit_amount": {
                            "value": parseFloat(price).toFixed(2),
                            "currency_code": base_currency_code
                        },
                        "sku": sku,
                        "quantity": qty
                    }
                })
                const data = {
                    firstname,
                    lastname,
                    telephone,
                    countryId,
                    region,
                    email,
                    orderItems
                }
                return JSON.stringify(data);
            } catch (error) {
                return {error:"Data not found"}
            }
        };

        return Component.extend({
            defaults: {
                template: 'Daytours_PayPalButtons/payment/form'
            },
            successPayment: ko.observable(false),
            initObservable: function () {
		this._super();
                return this;
            },
            initPayPalButton: function () {
                paypal.Buttons({
                    style: {
                        shape: 'pill',
                        color: 'blue',
                        layout: 'vertical',
                        label: 'paypal',
                    },
                    createOrder: this.createOrder.bind(this),
                    onApprove: this.onApprove.bind(this),
                    onError: this.onError.bind(this)
                }).render('#paypal-button-container');
            },
            createOrder: function (data, actions) {
                const agreementsChecked = document.getElementById("agreement_paypal_buttons_1")?.checked;
                if (!agreementsChecked) {
                    throw {
                        code: "AGREEMENTS_UNCHECKED",
                        message: "You must accept the terms and conditions to continue"
                    };
                }
                if (!additionalValidators.validate() || !quote.billingAddress?._latestValue) {
                    throw {
                        code: "BAD_BILLING_FIELDS",
                        message: "Please make sure you have completed all the fields correctly"
                    };
                }

                const { base_currency_code, base_grand_total, base_subtotal, base_discount_amount } = quote.totals();
                const orderItems = window.checkoutConfig.quoteItemData.map(({ name, price, qty, sku, options }) => {
                    return {
                        "name": name,
                        "unit_amount": {
                            "value": parseFloat(price).toFixed(2),
                            "currency_code": base_currency_code
                        },
                        "sku": sku,
                        "quantity": qty
                    }
                })

                return actions.order.create({
                    purchase_units:
                        [
                            {
                                "description": "Site: " + window.location.hostname,
                                "amount": {
                                    "currency_code": base_currency_code,
                                    "value": parseFloat(base_grand_total).toFixed(2),
                                    breakdown: {
                                        discount: {
                                            value: parseFloat(base_discount_amount * -1).toFixed(2),
                                            currency_code: base_currency_code
                                        },
                                        item_total: {
                                            value: parseFloat(base_subtotal).toFixed(2),
                                            currency_code: base_currency_code
                                        }
                                    }
                                },
                                "items": orderItems
                            }
                        ]
                }, {
                    "application_context": { "shipping_preference": 'NO_SHIPPING' }
                });
            },
            onApprove: async function (data, actions) {
                const element = document.getElementById('paypal-button-container');
                try {
                    const orderData = await actions.order.capture();
                    if (orderData) {
                        this.successPayment(true);
                        this.customPlaceOrder();
                        element.innerHTML = '';
                        element.innerHTML = '<h3>Thank you for your payment!</h3><br/>';
                        element.innerHTML += '<p>Please place the order to continue</p>';
                    }
                    else {
                        throw {
                            message: "Order data is empty"
                        }
                    }
                } catch (error) {
                    console.error(error.message, error);
                    element.innerHTML = '';
                    element.innerHTML = '<h3>Something Wrong</h3><br/>';
                    element.innerHTML += "<b>An error occurred while processing the order</b><br/><hr/>";
                    element.innerHTML += "<p>Error Details:</p><br>";
                    element.innerHTML += "<p>" + error.message + "</p>";

                    //ERROR LOGS ----------------------------------------------
                    errorLogs.sendErrorLog(
                        "Daytours_PayPalButtons",
                        error.message,
                        errorLogs.getCurrentLocationLineCode(new Error()),
                        getCustomerDataAsString()
                    );
                }
            },
            onError: function (err) {
                alert({
                    title: "Sorry! We couldn't process the payment",
                    content: err.message,
                    actions: {
                        always: function () { }
                    }
                });
                console.error(err.message, err);
                //ERROR LOGS ----------------------------------------------
                if(err?.code != "BAD_BILLING_FIELDS" && err?.code != "AGREEMENTS_UNCHECKED"){
                    errorLogs.sendErrorLog(
                        "Daytours_PayPalButtons",
                        err.message,
                        errorLogs.getCurrentLocationLineCode(new Error()),
                        getCustomerDataAsString()
                    );
                }
            },

            getCode: function () {
                return 'paypal_buttons';
            },

            customPlaceOrder(data = {}, event = new Event("click")) {
                try {
                    if (!this.successPayment()) {
                        throw {
                            message: "Successful payment was not detected"
                        }
                    }
                    this.placeOrder(data, event);
                } catch (err) {
                    alert({
                        title: "Sorry! An error occurred when placing the order",
                        content: err.message,
                        actions: {
                            always: function () { }
                        }
                    });
                    //ERROR LOGS ----------------------------------------------
                    errorLogs.sendErrorLog(
                        "Daytours_PayPalButtons",
                        err.message,
                        errorLogs.getCurrentLocationLineCode(new Error()),
                        getCustomerDataAsString()
                    );
                }
            },
            

            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'transaction_result': "Success"
                    }
                };
            },
        });
    }
);
