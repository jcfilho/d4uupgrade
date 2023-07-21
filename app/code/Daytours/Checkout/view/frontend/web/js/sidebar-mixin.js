define([
    'jquery',
    'Magento_Customer/js/model/authentication-popup',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/lib/core/storage/local',
    'mage/url',
    'overlay-minicart',
    'jquery/ui',
    'mage/decorate',
    'mage/collapsible',
    'mage/cookies'
],function($, authenticationPopup, customerData, alert, confirm,storage,urlbuild,overlayMinicart){

    return function (originalWidget) {
        $.widget('daytours.sidebar',$.mage.sidebar,{
            _initContent: function () {
                var self = this,
                    events = {};

                this.element.decorate('list', this.options.isRecursive);

                /**
                 * @param {jQuery.Event} event
                 */
                events['click ' + this.options.button.close] = function (event) {
                    event.stopPropagation();
                    $(self.options.targetElement).dropdownDialog('close');
                    overlayMinicart.closeOverlay();
                };
                events['click ' + this.options.button.checkout] = $.proxy(function () {
                    var cart = customerData.get('cart'),
                        customer = customerData.get('customer');

                    if (!customer().firstname && cart().isGuestCheckoutAllowed === false) {
                        // set URL for redirect on successful login/registration. It's postprocessed on backend.
                        $.cookie('login_redirect', this.options.url.checkout);

                        if (this.options.url.isRedirectRequired) {
                            location.href = this.options.url.loginUrl;
                        } else {
                            // authenticationPopup.showModal();
                            $("body .popup-with-form").trigger('click');
                            var linkUrlCheckout = urlbuild.build('checkout');
                            $("#currentUrl").val(linkUrlCheckout);
                        }

                        return false;
                    }
                    location.href = this.options.url.checkout;
                }, this);

                /**
                 * @param {jQuery.Event} event
                 */
                events['click ' + this.options.button.remove] =  function (event) {
                    event.stopPropagation();
                    confirm({
                        content: self.options.confirmMessage,
                        actions: {
                            /** @inheritdoc */
                            confirm: function () {
                                self._removeItem($(event.currentTarget));
                            },

                            /** @inheritdoc */
                            always: function (e) {
                                e.stopImmediatePropagation();
                            }
                        }
                    });
                };

                /**
                 * @param {jQuery.Event} event
                 */
                events['keyup ' + this.options.item.qty] = function (event) {
                    self._showItemButton($(event.target));
                };

                /**
                 * @param {jQuery.Event} event
                 */
                events['click ' + this.options.item.button] = function (event) {
                    event.stopPropagation();
                    self._updateItemQty($(event.currentTarget));
                };

                /**
                 * @param {jQuery.Event} event
                 */
                events['focusout ' + this.options.item.qty] = function (event) {
                    self._validateQty($(event.currentTarget));
                };

                this._on(this.element, events);
                this._calcHeight();
                this._isOverflowed();
            }
        });

        return $.daytours.sidebar;
    }
});