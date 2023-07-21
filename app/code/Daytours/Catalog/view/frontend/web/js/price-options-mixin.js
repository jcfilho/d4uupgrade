



/*
*
* DOESN'T USE BECAUSE FIREFOX HAS PROBLEMS WITH MIXING
*
* */




define([
    'jquery',
    'underscore',
    'mage/template',
    'priceUtils',
    'priceBox',
    'jquery/ui'
], function ($, _, mageTemplate, utils) {
    return function(originalWidget){
        // if you want to get fancy and pull the widget namespace
        // and name from the returned widget definition
        // var widgetFullName = originalWidget.prototype.namespace +
        //     '.' +
        //     originalWidget.prototype.widgetName;

        function defaultGetOptionValue(element, optionsConfig) {
            var changes = {},
                optionValue = element.val(),
                optionId = utils.findOptionId(element[0]),
                optionName = element.prop('name'),
                optionType = element.prop('type'),
                optionConfig = optionsConfig[optionId],
                optionHash = optionName;

            switch (optionType) {
                case 'text':
                case 'textarea':
                    changes[optionHash] = optionValue ? optionConfig.prices : {};
                    break;

                case 'radio':
                    if (element.is(':checked')) {
                        changes[optionHash] = optionConfig[optionValue] && optionConfig[optionValue].prices || {};
                    }
                    break;

                case 'select-one':
                    changes[optionHash] = optionConfig[optionValue] && optionConfig[optionValue].prices || {};
                    changes['elementSelectorDt'] = {
                     'element' : element,
                     'amount' : 0
                    };
                    break;

                case 'select-multiple':
                    _.each(optionConfig, function (row, optionValueCode) {
                        optionHash = optionName + '##' + optionValueCode;
                        changes[optionHash] = _.contains(optionValue, optionValueCode) ? row.prices : {};
                    });
                    break;

                case 'checkbox':
                    optionHash = optionName + '##' + optionValue;
                    changes[optionHash] = element.is(':checked') ? optionConfig[optionValue].prices : {};
                    break;

                case 'file':
                    // Checking for 'disable' property equal to checking DOMNode with id*="change-"
                    changes[optionHash] = optionValue || element.prop('disabled') ? optionConfig.prices : {};
                    break;
            }

            return changes;
        }


        $.widget(
            'mage.priceOptions',//named widget we're redefining

            //jQuery.mage.dropdownDialog
            $['mage']['priceOptions'],   //widget definition to use as
            //a "parent" definition -- in
            //this case the original widget
            //definition, accessed using
            //bracket syntax instead of
            //dot syntax

            {//the new methods
                /**
                 * Custom option change-event handler
                 * @param {Event} event
                 * @private
                 */
                _onOptionChanged: function onOptionChanged(event) {
                    var changes,
                        option = $(event.target),
                        handler = this.options.optionHandlers[option.data('role')];
                    option.data('optionContainer', option.closest(this.options.controlContainer));

                    if (handler && handler instanceof Function) {
                        changes = handler(option, this.options.optionConfig, this);
                    } else {
                        changes = defaultGetOptionValue(option, this.options.optionConfig);
                    }

                    $(this.options.priceHolderSelector).trigger('updatePrice', changes);
                }
            });

        //return the redefined widget for `data-mage-init`
        //jQuery.mage.dropdownDialog
        return $['mage']['priceOptions'];
    };
});