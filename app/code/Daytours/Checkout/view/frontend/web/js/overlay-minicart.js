define(
    [
        'jquery'
    ],function($){

        var openOverlay = function(){
            $('.overlay.overlay01').addClass('is-open');
            $('body').css({
                overflow : 'hidden'
            });
            $('header.page-header .logo').addClass('zindex1');
        };
        var closeOverlay = function(){
            $('body').css({
                overflow : 'inherit'
            });
            $('.overlay.overlay01').removeClass('is-open');
            $('header.page-header .logo').removeClass('zindex1');
        };

        return {
            openOverlay : function(){
                openOverlay();
            },
            closeOverlay : function(){
                closeOverlay();
            },
            clickOverlayMinicart: function(){
                $('.overlay.overlay01').on('click',function(){
                    closeOverlay();
                });
            }
        }
    });