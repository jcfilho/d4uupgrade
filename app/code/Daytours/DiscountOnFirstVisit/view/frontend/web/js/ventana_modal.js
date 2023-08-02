require(
    [
        'jquery',
        'Magento_Ui/js/modal/modal',
        'mage/translate',
        'jquery/jquery.cookie'
    ],
    function ($, modal, $t) {
        let modalElement;
        var baseUrl = "";
        const regEmail = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            buttton: null,
            closed:function(){$.cookie('first-visit',"false",{expires:21});}
        };
        $("document").ready(() => {
            let firstVisit = $.cookie('first-visit');
            const language = document.querySelector('html').lang;
            baseUrl = $("#baseUrl").val();
            if (firstVisit != "false") {
                modalElement = $('<div id="modal_content"></div>');
                $('document').append(modalElement);
                modal(options, modalElement);
                //https://fc9bfe9ef1.nxcli.net/pub/media/modal_primera_visita/thank_you.png

                setTimeout(() => {
                    modalElement.modal('openModal');
                    $("div.modal-inner-wrap > footer").addClass("first-visit-footer");
                    $("div.modal-inner-wrap").addClass("first-visit-wrap");
                    $("div.modal-inner-wrap").attr("style", 'background-image:url(https://www.daytours4u.com/pub/media/modal_primera_visita/dt4u_'+language+'.png)');
                    $('div.modal-inner-wrap > footer').html(`\
                    <form id="registro-usuario-primera-visita" action="#">\
                        <input type="email" class="input-modal" placeholder="${$.mage.__('Email')}" id="email-first-visit" /><br/>\
                        <input type="password" class="input-modal" placeholder="${$.mage.__('Password')}" id="password-first-visit"/><br/>\
                        <input type="password" class="input-modal" placeholder="${$.mage.__('Confirm Password')}" id="password-confirmation-first-visit"/>\
                        <button class="button-registrar">${$.mage.__('Create an Account')}</button>\
                    </form>`);
                    $('#registro-usuario-primera-visita').on('submit', registrarUsuario);
                    //$.cookie('first-visit',"false",{expires:21});
                }, 1000);
            }
        });
        registrarUsuario = (e) => {
            e.preventDefault();
            const email = $('#email-first-visit').val();
            const password = $('#password-first-visit').val();
            const passwordConfirmation = $('#password-confirmation-first-visit').val();
            if (!regEmail.test(email)) { alert("Please, enter a valid email"); }
            else if (password.length < 6) { alert("The password must be at least 6 characters"); }
            else if (!(/\d/.test(password))) { alert("The password must contain numbers"); }
            else if (password !== passwordConfirmation) { alert("Passwords entered do not match"); }
            else {
                let form = new FormData();
                form.append("email", email);
                form.append("password", password);
                form.append("password_confirmation", passwordConfirmation);
                form.append("isAjax", true);
                modalElement.modal('closeModal');
                
                $.ajax({
                    dataType: "json",
                    type: "POST",
                    url: baseUrl + 'customer/account/createpost/',
                    showLoader: false,
                    processData: false,
                    contentType: false,
                    cache: false,
                    enctype: 'multipart/form-data',
                    data: form,
                    success: function (response) {
                        const obj = JSON.parse(JSON.stringify(response));
                        if (obj.success == 1) {
                            resultadoModal(true, "Registracion exitosa");
                            setTimeout(() => {
                                $("#social-register-form .ajaxregisterSuccess").show();
                                $("#social-register-form .ajaxregisterSuccess .message-success div").html(obj.message);
                                $("#social-register-form .social-creates,.block.social-login-authentication-channel.account-social-login,.ajaxregisterSuccess .block-title,.ajaxregister.white-popup-block .block-title.block-title-sign-in,.ajaxregister.white-popup-block .mfp-close,.ajaxregister-content .back-login").hide();
                                /*Redirect to wishlist if fhag is active*/
                                if (typeof window.localStorage.appData !== 'undefined') {
                                    var json = JSON.parse(window.localStorage.appData);
                                    if (typeof json.data_wishlist_to_redirect !== "undefined") {
                                        if (json.data_wishlist_to_redirect) {
                                            storage.remove('data_wishlist_to_redirect');
                                            $.mage.dataPost().postData(json.data_wishlist_to_redirect);
                                            return;
                                        }
                                    }
                                }
                                let url = '';
                                if (typeof obj.backUrl !== 'undefined' && obj.backUrl) {
                                    $("#currentUrl").val(obj.backUrl)
                                }
                                var isLoginRedirect = $("#Mageants-sociallogin-popup #isLoginRedirect").val();

                                if (isLoginRedirect == 1) {
                                    url = $("#currentUrl").val();
                                    window.location.replace(url);
                                    return;
                                }
                                else {
                                    url = $("#baseUrl").val() + "customer/account";
                                    window.location.replace(url);
                                    return;
                                }
                            }, 5000);
                        }
                        if (obj.excep) {
                            resultadoModal(false, obj.excep);
                        }
                        else {
                            $("#social-register-form span.ajaxregisterError").html('');
                            $("#social-register-form span.ajaxregisterError").append(obj.success).css({ display: 'block' });
                            $("#social-register-form span.ajaxregisterError").show();
                            reloadCaptcha();
                        }
                    },
                    error: function (xhr, status, error) {
                        try {
                            var err = eval("(" + xhr.responseText + ")");
                            console.log(err);
                        } catch (error) {
                            console.log(xhr.responseText);
                        }
                        resultadoModal(false, "Ocurrio un error al registrarse");
                    }
                });
            }

        }

        const resultadoModal = (exitosa, mensaje = "") => {
            let avisoElement = $('<div id="modal_resultado_registracion"></div>');
            modal(options, avisoElement);
            avisoElement.modal('openModal');
            $('.modal-popup.modal-slide').addClass('popup-resultado');
            if (exitosa) {
                $('.modal-inner-wrap').attr('style','background: transparent;box-shadow:none;');
                $('.modal-footer').html('style','display:none !important;');
                $('.modal-content').html(`<img src="https://www.daytours4u.com/pub/media/modal_primera_visita/thank_you.png" style="border-radius:10px;box-shadow: 0px 0px 10px -5px black;margin:10px;">`);
            }
            else {
                $('.modal-content').html(`<p class="label-result error">${mensaje}</p>`);
                $('.modal-footer').attr("style", "display:block !important;");
            }
            $('.action-close').attr("style", "display:none !important;");
            
        }
    }
);