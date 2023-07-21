require(
    [
        'jquery',
        'Magento_Ui/js/lib/core/storage/local'
    ],
    function($,storage){
        'use strict';
        var AppWishlistNotLoagged = function () {

        };

        AppWishlistNotLoagged.prototype = {
            selector : {
                sElementToAddWishlist : '.action.towishlist'
            },
            init:function(){
                var self = this;
                self.addEventToWishlistNotLogged();
            },
            addEventToWishlistNotLogged : function () {
                var self = this;
                $(self.selector.sElementToAddWishlist).off('click');
                $('body').on('click',self.selector.sElementToAddWishlist,function (event) {
                    event.preventDefault();
                    var postData = $(this).data('post');
                    postData.data.wishlish_not_logged = true;
                    //postData.data.redirectFromWishlist = window.location.href;
                    storage.set('data_wishlist_to_redirect',postData);
                    $("body .popup-with-form").trigger('click');
                    return false;
                });
            }
        };

        $(document).ready(function () {
            var app = new AppWishlistNotLoagged();
            app.init();
        });
    });