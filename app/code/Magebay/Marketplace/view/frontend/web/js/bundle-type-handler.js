/*jshint browser:true jquery:true expr:true*/
define([
    'jquery',
    'Magebay_Marketplace/catalog/type-events',
    'Magebay_Marketplace/js/product/weight-handler'
], function ($, productType, weight) {
    'use strict';

    return {

        /**
         * Constructor component
         */
        'Magebay_Marketplace/js/bundle-type-handler': function () {
            this.bindAll();
            this._initType();
        },

        /**
         * Bind all
         */
        bindAll: function () {
            $(document).on('changeTypeProduct', this._initType.bind(this));
        },

        /**
         * Init type
         * @private
         */
        _initType: function () {
            if (
                productType.type.real === 'bundle' &&
                productType.type.current !== 'bundle' &&
                !weight.isLocked()
            ) {
                weight.switchWeight();
            }
        }
    };
});
