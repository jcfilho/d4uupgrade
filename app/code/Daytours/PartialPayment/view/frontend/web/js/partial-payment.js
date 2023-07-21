define([
    "jquery",
    "uiComponent",
    "ko",
    "Magento_Ui/js/modal/alert",
    "Daytours_ErrorLogs/js/model/errorLogs",
    "Magento_Customer/js/customer-data",
], function ($, Component, ko, alert, errorLogs, customerData) {
    "use strict";
    let payPalButtonsInitialized = false;
    const getPayPalButtonHTML = () => {
        let buttonPaypal = document.createElement("section");
        buttonPaypal.setAttribute("id", "smart-button-container");
        buttonPaypal.innerHTML = `
        <hr/>
        <div  style="text-align: center;">
        <div id="paypal-button-container-partial-payment"></div>
        </div>
        </div>
        `;
        return buttonPaypal.outerHTML;
    };

    const initPayPalButton = (amount, orderId, protectId) => {
        if (payPalButtonsInitialized) {
            return;
        }
        payPalButtonsInitialized = true;

        const reportError = (error, title = "Error") => {
            var customer = customerData.get("customer");
            alert({
                title: $.mage.__(title),
                content: $.mage.__(error.message),
                actions: { always: function () {} },
            });
            if(errorLogs){
                errorLogs.sendErrorLog(
                    "Daytours_PartialPayment",
                    error?.message || "",
                    error?.stack ? errorLogs.getCurrentLocationLineCode(error) : "",
                    JSON.stringify(customer())
                );
            }
        };    

        try {
            const createOrder = (data, actions) => {
                try {
                    let purchaseUnits = [
                        {
                            description:
                                "Due amount payment of order" +
                                orderId +
                                " .Site: " +
                                window.location.hostname,
                            amount: {
                                currency_code: "USD",
                                value: parseFloat(amount).toFixed(2),
                            },
                        },
                    ];

                    return actions.order.create(
                        {
                            purchase_units: purchaseUnits,
                        },
                        {
                            application_context: {
                                shipping_preference: "NO_SHIPPING",
                            },
                        }
                    );
                } catch (error) {
                    reportError(
                        error,
                        "Error. PayPal Order could not be created"
                    );
                }
            };

            const onApprove = (data, actions) => {
                
                return actions.order
                    .capture()
                    .then(async (details) => {
                        try {
                            let res = await fetch(
                                window.location.origin +
                                    "/rest/V1/partialpayment/orders?protectId=" +
                                    protectId,
                                {
                                    method: "POST",
                                    headers: {
                                        Accept: "application/json",
                                        "Content-Type": "application/json",
                                    },
                                }
                            );
                            let resJson = await res.json();
                            resJson = JSON.parse(resJson);
                            if (resJson.status === "200") {
                                alert({
                                    title: $.mage.__(
                                        "Payment has done successfully"
                                    ),
                                    actions: {
                                        always: function () {
                                            window.location.reload();
                                        },
                                    },
                                });
                            } else if (resJson.status === "404") {
                                throw new Error(resJson.message);
                            } else if (resJson.status === "500") {
                                throw new Error(resJson.message);
                            } else {
                                throw new Error(resJson.message);
                            }
                        } catch (error) {
                            reportError(error, "Payment Registration Error");
                        }
                    })
                    .catch((error) => {
                        reportError(error, "Payment Processing Error");
                    });
            };

            const onError = (err) => {
                reportError(err, "PayPal Error");
            };

            paypal
                .Buttons({
                    style: {
                        shape: "pill",
                        color: "blue",
                        layout: "vertical",
                        label: "paypal",
                    },
                    createOrder: createOrder,
                    onApprove: onApprove,
                    onError: onError,
                    env: "sandbox", // sandbox | production
                    client: {
                        sandbox:
                            "AaRUEv3WcCmzykig6K6tGscVjsSmgCBgo0nf3Dyyfq-d1-UhdoLGCY6wzMVWHT7arBeVNvG-gmHH6fbU",
                        production:
                            "AVrYNSEWkHloe1DES1CU_U6gPbu_8XMTYev2g8iPeITNDavdHGwJ7xtO49hB5VCufXpG7Q70ovcBvoLr",
                    },
                })
                .render("#paypal-button-container-partial-payment");
        } catch (err) {
            reportError(err, "PayPal Initialization Error");
        }
    };

    const showDetails = async (orderId) => {
        try {
            $("body").trigger('processStart');

            $.ajax({
                url: window.location.origin+"/rest/V1/partialpayment/orders?orderId="+orderId,
                type: 'GET',
                dataType: 'json',
                showLoader: true
            }).done((data)=>{
                // $(".msg").text(data.msg);
                if (!data && !data[1] && !data[1][0]) {
                    throw new Error(data);
                }
                const order = data[1][0];

                alert({
                    title: $.mage.__("Order #")+ order.entity_id,
                    modalClass: "payment-modal",
                    content:
                    "*"+$.mage.__("All amounts has been converted to USD currency")+"<br/>"+
                        "<b>"+$.mage.__("Due Amount")+": $" +
                        (+order.base_total_due)?.toFixed(2) + "</b>" +
                        "<br/>"+$.mage.__("Paid Amount")+": $" +
                        ((+order.base_total_paid)?.toFixed(2) || 0) +
                        "<br/>"+$.mage.__("Total")+": $" +
                        (+order.base_grand_total).toFixed(2) +
                        getPayPalButtonHTML(),
                    actions: {
                        always: () => {
                            payPalButtonsInitialized = false;
                        },
                    },
                    buttons: [
                        {
                            text: $.mage.__("Cancel"),
                            class: "action-primary action-accept",
                            click: function () {
                                this.closeModal(true);
                            },
                        },
                        {
                            text: $.mage.__("Continue"),
                            class: "action-primary action-accept",
                            click: () => {
                                initPayPalButton(
                                    order.base_total_due,
                                    order.entity_id,
                                    order.protect_code
                                );
                            },
                        },
                    ],
                });
            })
        } catch (error) {
            reportError(error, "Unknown Error");
        }
        finally{
            $("body").trigger('processStop');
        }
    }

    return Component.extend({
        defaults: {
            template: "Daytours_PartialPayment/partial-payment-orders",
        },
        initialize: function () {
            this._super();
        },
        showDetails: showDetails
    });
});
