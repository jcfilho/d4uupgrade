var config = {
    "map": {
        "*": {
            "Magento_Checkout/template/billing-address.html":
                "Daytours_Checkout/template/billing-address.html",
            "overlay-minicart" : "Daytours_Checkout/js/overlay-minicart"
        }
    },
    "config": {
        "mixins" : {
            "Magento_Checkout/js/sidebar":{
                "Daytours_Checkout/js/sidebar-mixin" : true
            },
            "Magento_Checkout/js/proceed-to-checkout":{
                "Daytours_Checkout/js/proceed-to-checkout-mixin" : true
            },
            "Magento_Checkout/js/view/form/element/email":{
                "Daytours_Checkout/js/view/form/element/email" : true
            }
        }
    }
};
