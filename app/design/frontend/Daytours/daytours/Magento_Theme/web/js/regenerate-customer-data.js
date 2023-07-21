define(
    [
        'jquery',
        'Magento_Customer/js/customer-data'
    ],
    function($,customerData){
        'use strict';
        $(document).ready(function(){
            customerData.reload(['customer'], true);
        });
    });