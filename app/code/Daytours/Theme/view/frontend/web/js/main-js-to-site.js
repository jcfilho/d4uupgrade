/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'js/owl.carousel',
    'js/slick.min',
    'mage/translate',
    'mage/url',
    'js/jquery.sticky'
], function ($,carousel,slick,$tr,urlBuilder,$sticky) {
    'use strict';

    var AppMain = function () {

    };

    AppMain.prototype = {
        selectors : {
            textSearhBanner : $(".form.minisearch #search"),
            joinAdventureColumn1 : $('.catecolumn-1'),
            joinAdventureColumn2 : $('.catecolumn-2'),
            inputDatesCustomOption : $('.product-custom-option.datetime-picker.input-text._has-datepicker')
        },
        init: function () {
            var self = this;
            self.changeTextOnSearcheader();
            self.sameHeightAdventure();
            self.customOptionDateReadOnly();
            self.clickToActivitiesHome();
            self.resizeWindow();
            self.removeRountripButton();
            self.clickToPixelFacebook();
            $("document").ready(() => {
                setTimeout(()=>{
                    self.sameHeightAdventure();
                    if(!location.href.includes('travel-guide')){
                       // self.createWhatsAppButton();
                    }
                },1500)
            });
        },
        /*createWhatsAppButton: function () {
            var options = {
                facebook: "929014450496559", // Facebook page ID
                whatsapp: "+5491132155641", // WhatsApp number
                //telegram: "TangolTours", // Telegram user
                company_logo_url: "//www.daytours4u.com/pub/media/logo/default/logo2.png", // URL of company logo (png, jpg, gif)
                greeting_message: "Hello, How May I Help You?", // Text of greeting message
                call_to_action: "Chat on Whatsapp or Facebook", // Call to action
                button_color: "#33CEFF", // Color of button
                position: "right", // Position may be 'right' or 'left'
                order: "facebook,whatsapp", // Order of buttons
                ga: true, // Google Analytics enabled
                branding: false, // Show branding string
                mobile: true, // Mobile version enabled
                desktop: true, // Desktop version enabled
                greeting: true, // Greeting message enabled
                shift_vertical: 0, // Vertical position, px
                shift_horizontal: 0, // Horizontal position, px
                domain: "daytours4u.com", // site domain
                key: "PdQe9SxOTK6b937NY1TfpQ", // pro-widget key
            };
            if(document.URL.includes("/es")){
                options.greeting_message = "Hola, ¿Cómo Puedo Ayudarte?";
                options.call_to_action = "Chatea en Whatsapp o Facebook";
            }
            else if(document.URL.includes("/fr")){
                options.greeting_message = "Bonjour, Comment Pouvons-nous Vous Aider?";
                options.call_to_action = "Chattez sur Whatsapp ou Facebook";
            }
            else if(document.URL.includes("/pt")){
                options.greeting_message = "Olá, Como Podemos Ajudá-lo?";
                options.call_to_action = "Converse no Whatsapp ou Facebook";
            }
            var proto = document.location.protocol, host = "getbutton.io", url = proto + "//static." + host;
            var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = url + '/widget-send-button/js/init.js';
            s.onload = function () { WhWidgetSendButton.init(host, proto, options); };
            var x = document.getElementsByTagName('script')[0]; x.parentNode.insertBefore(s, x);
        },*/
        changeTextOnSearcheader : function () {
            var self = this;
            var textSearch = $.mage.__('Search Tours and Activities');
            if($( document ).width() < 465){
                textSearch = $.mage.__('Search...');
            }
            self.selectors.textSearhBanner.attr('placeholder',textSearch);
        },
        removeRountripButton: function () {
            let contador1 = 0;
            let interval = setInterval(() => {
                let title = document.querySelector("#maincontent > div > div.column.main > div.product-info-main > div.page-title-wrapper.product > h1 > span");
                if (title != null) {
                    let titleText = title.innerText.toLowerCase();
                    if (titleText.includes("private") || titleText.includes("privado") || titleText.includes("prive") || location.href.includes("private") || location.href.includes("prive") || location.href.includes("privado")) {
                        let roundtripRow = "#product_addtocart_form > div.main-form > div.product-info-main > div.product-add-form > div.simple-booking-form.booking-form > div.row";
                        let contador = 0;
                        let checker = window.setInterval(function () {
                            if (document.querySelector(roundtripRow) != null) {
                                clearInterval(checker);
                                //console.log("SELECT encontrado");
                                document.querySelector(roundtripRow).style.display = "none";
                            }
                            else if (contador >= 10) {
                                clearInterval(checker);
                            }
                            contador++;
                        }, 200);
                    }
                }
                if (contador1 >= 12) {
                    clearInterval(interval);
                }
                contador1++;
            }, 200);
        },
        sameHeightAdventure : function () {
            var self = this;
            if( self.selectors.joinAdventureColumn1.length > 0 ){
                if($(window).width() >= 768){
                    self.selectors.joinAdventureColumn2.css({
                        height : self.selectors.joinAdventureColumn1.height(),
                        overflow: 'hidden'
                    })
                }else{
                    self.selectors.joinAdventureColumn2.css({
                        height : '100%',
                        overflow: 'none'
                    })
                }
            }
        },
        customOptionDateReadOnly: function(){
            var self = this;
            if( self.selectors.inputDatesCustomOption.length > 0 ){
                self.selectors.inputDatesCustomOption.prop('readonly',true);
            }
        },
        resizeWindow : function () {
            var self = this;
            $( window ).resize(function() {
                self.changeTextOnSearcheader();
                self.sameHeightAdventure();
            });
        },
        clickToActivitiesHome: function(){
            var self = this;
            $('.cms-homepage').on('click','.activities-item',function(){
                var href = $(this).find('.activities-link').attr('href');
                window.location = href;
            });
        },
        clickToPixelFacebook: function () {
            var self = this;
            $('.page-header .custom-link .chatus').on('click', function(){
                fbq('track', 'Contact');
            });
        }
    };


    $(document).ready(function(){

        var appMainExec = new AppMain();
        appMainExec.init();

        //Reload customer data


        /* Search box */
        $(".page-header .header").on('click','.block-search .block-title',function () {
            $('body').toggleClass('searchbox-open');
        });

        /* Footer pestañas */
        $('footer.page-footer').on('click','h2.footer-column__title',function () {
            $(this).parent().toggleClass('active');
        });

        /*POPUP authentication*/
        setInterval(function() {
            $(".popup-authentication .field.field-customstyle").each(function(){
                if($(this).find('input').val() != ""){
                    $(this).addClass('enter');
                } else {
                    $(this).removeClass('enter');
                }
            });
        }, 500);
        $(".popup-authentication .field.field-customstyle input").change(function () {
            if($(this).val() != ""){
                $(this).parents('.field-customstyle').addClass('enter');
            } else {
                $(this).parents('.field-customstyle').removeClass('enter');
            };
        });

        /**/
        $('body').on('click','.product-des-content.content-compact + .view-more',function () {
            $(this).prev('.product-des-content.content-compact').toggleClass('show-full');
        });
        $('body').on('click','.block-product-des .product-des-title',function () {
            $(this).parent('.block-product-des').toggleClass('opened');
        });
        $('body').on('click','.block.review-list > .block-title',function () {
            $(this).parent('.review-list').toggleClass('opened');
        });
        /* Clear Search */

        $('body').on('click','#clear-search', function () {
            $(this).closest('#search_mini_form').find('#search').val('');
            $('body input#search').focus();
        });

        // Sortby Click
        $('body').on('click','.sorter-label',function () {
            $(this).parent().toggleClass('active');
        });

        //Summary order in success page
        $('body').on('click','.summary_cart_success .list_product .product .detail .product_options .label',function () {
            $(this).toggleClass('active');
            $(this).next('.values').slideToggle();
        });

        var page_header_height = $('.page-header').height();

        $("#faqs-block .block-collapsible-nav.collapsible-custom").sticky({topSpacing: page_header_height});
        if ($("#faqs-block .block-collapsible-nav.collapsible-custom").length) {
            $('footer.page-footer').css({'z-index': 9});
        }



    });



    /* Header fixed */
    $(window).scroll(function(){
        if($(window).scrollTop() > 0){
            $('body').addClass('header-fixed');
        }
        else {
            $('body').removeClass('header-fixed');
        }
        if($(window).width() < 768){
            if( $('.block-whydaytours4u').length > 0 ){
                if($(window).scrollTop() >= $('.block-whydaytours4u').offset().top){
                    $('body').addClass('fixed-booknow');
                }
                else {
                    $('body').removeClass('fixed-booknow');
                }
            }
        }
    });



    /* Same Product Height + Line Heading */
    function sameheight(classitem, classchild) {
        var i=0;
        var height=[];
        classitem.find(classchild).css('min-height','0');
        classitem.each(function(){
            height[i] = $(this).find('.product-item-infomain').height();
            i++;
        });
        var maxheight = Math.max.apply(Math, height);
        classitem.find(classchild).css('min-height',maxheight);
    };
    function lineheading(){
        if($(window).width() > 1330){
            $('.ves-container h2.w_heading span.line-heading').css('left',-($(window).width() - 1270)/2);
        } else if($(window).width() < 768){
            $('.ves-container h2.w_heading span.line-heading').css('left','-25px');
        } else{
            $('.ves-container h2.w_heading span.line-heading').css('left','-30px');
        };
    };
    $(window).on('load', function() {
        sameheight($('.dealproduct-widget .owl-carousel.product-items .owl-item'), '.product-item-infomain');
        sameheight($('.aside-wrapper .owl-carousel.product-items .owl-item'), '.product-item-infomain');
        lineheading();
    });
    $( window ).resize(function() {
        sameheight($('.dealproduct-widget .owl-carousel.product-items .owl-item'), '.product-item-infomain');
        sameheight($('.aside-wrapper .owl-carousel.product-items .owl-item'), '.product-item-infomain');
        lineheading();
    });
    setInterval(function() {
        sameheight($('.dealproduct-widget .owl-carousel.product-items .owl-item'), '.product-item-infomain');
        sameheight($('.aside-wrapper .owl-carousel.product-items .owl-item'), '.product-item-infomain');
    }, 500);


    // FAQs page
    function goToByScroll(id){
        id = id.replace("link", "");
        var page_header_height = $('.page-header').height() + 25;
        // Scroll
        $('html,body').animate({
            scrollTop: $(id).offset().top - page_header_height
        },600);
    }

    $("body").on('click','.block-collapsible-nav.collapsible-custom .item a',function(e) {
        e.preventDefault();
        goToByScroll($(this).attr('href'));
    });

    $(window).scroll(function() {
        var scrollDistance = $(window).scrollTop();

        $('.faq-tabs .faq-tab').each(function(i) {
            if ($(this).position().top <= scrollDistance) {
                $('.block-collapsible-nav.collapsible-custom .item.current').removeClass('current');
                $('.block-collapsible-nav.collapsible-custom .item').eq(i).addClass('current');
            }
        });
    }).scroll();

    //End FAQs page


});
