document.addEventListener("DOMContentLoaded", function () {
    const initWappButton = function (e, t) {
        "use strict";
        console.log("Valor de e",e);
    
        Math.imul = Math.imul || function (e, t) {
            var a = 65535 & e,
                i = 65535 & t;
            return a * i + ((e >>> 16 & 65535) * i + a * (t >>> 16 & 65535) << 16 >>> 0) | 0
        }, t.wame_public = t.wame_public || {}, wame_public = e?.extend({
            $wame: null,
            $badge: null,
            settings: null,
            store: null,
            chatbox: !1,
            is_mobile: !1
        }, wame_public), wame_public.send_event = function (e) {
            var a = t[wame_public.settings.ga_tracker || "ga"];
            "function" == typeof a && "function" == typeof a.getAll ? (a("set", "transport", "beacon"), a.getAll().forEach(function (t) {
                t.send("event", "WhatsAppMe", "click", e)
            })) : "function" == typeof gtag && gtag("event", "click", {
                event_category: "WhatsAppMe",
                event_label: e,
                transport_type: "beacon"
            });
            "object" == typeof dataLayer && dataLayer.push({
                event: "WhatsAppMe",
                eventAction: "click",
                eventLabel: e
            }), "function" == typeof fbq && fbq("trackCustom", "WhatsAppMe", {
                eventAction: "click",
                eventLabel: e
            })
        }, wame_public.hash = function (e) {
            for (var t = 0, a = 1; t < e.length; t++) a = Math.imul(a + e.charCodeAt(t) | 0, 2654435761);
            return (a ^ a >>> 17) >>> 0
        }, wame_public.whatsapp_link = function (e, t, a) {
            return ((a = void 0 !== a ? a : wame_public.settings.whatsapp_web && !wame_public.is_mobile) ? "https://web.whatsapp.com/send" : "https://api.whatsapp.com/send") + "?phone=" + encodeURIComponent(e) + "&text=" + encodeURIComponent(t || "")
        }, wame_public.chatbox_show = function () {
            wame_public.$wame.addClass("whatsappme--chatbox"), wame_public.chatbox = !0, wame_public.settings.message_badge && wame_public.$badge.hasClass("whatsappme__badge--in") && wame_public.$badge.toggleClass("whatsappme__badge--in whatsappme__badge--out"), e(document).trigger("whatsappme:show")
        }, wame_public.chatbox_hide = function () {
            wame_public.$wame.removeClass("whatsappme--chatbox whatsappme--tooltip"), wame_public.chatbox = !1, e(document).trigger("whatsappme:hide")
        }, wame_public.save_hash = function (e) {
            var t = (wame_public.store.getItem("whatsappme_hashes") || "").split(",").filter(Boolean); - 1 == t.indexOf(e) && (t.push(e), wame_public.store.setItem("whatsappme_hashes", t.join(",")))
        }, e(function () {
            wame_public.$wame = e(".whatsappme"), wame_public.$badge = wame_public.$wame.find(".whatsappme__badge"), wame_public.settings = wame_public.$wame.data("settings"), wame_public.is_mobile = !!navigator.userAgent.match(/Android|iPhone|BlackBerry|IEMobile|Opera Mini/i);
            try {
                localStorage.setItem("test", 1), localStorage.removeItem("test"), wame_public.store = localStorage
            } catch (e) {
                wame_public.store = {
                    _data: {},
                    setItem: function (e, t) {
                        this._data[e] = String(t)
                    },
                    getItem: function (e) {
                        return this._data.hasOwnProperty(e) ? this._data[e] : null
                    }
                }
            }
            if (void 0 === wame_public.settings) try {
                wame_public.settings = JSON.parse(wame_public.$wame.attr("data-settings"))
            } catch (e) {
                wame_public.settings = void 0
            }
            wame_public.$wame.length && wame_public.settings && wame_public.settings.telephone && function () {
                var a, i, s = 1e3 * wame_public.settings.button_delay,
                    n = 1e3 * wame_public.settings.message_delay,
                    p = !!wame_public.settings.message_text,
                    c = (wame_public.store.getItem("whatsappme_hashes") || "").split(",").filter(Boolean),
                    o = "yes" == wame_public.store.getItem("whatsappme_visited"),
                    m = p ? wame_public.hash(wame_public.settings.message_text).toString() : "no_cta",
                    l = c.indexOf(m) > -1;

                function u() {
                    clearTimeout(i), wame_public.chatbox_show()
                }

                function w() {
                    wame_public.save_hash(m), wame_public.chatbox_hide()
                }

                function _() {
                    if (p && !wame_public.chatbox) u();
                    else {
                        var a = {
                            link: wame_public.whatsapp_link(wame_public.settings.telephone, wame_public.settings.message_send)
                        },
                            i = new RegExp("^https?://(wa.me|(api|web|chat).whatsapp.com|" + location.hostname.replace(".", ".") + ")/.*", "i");
                        wame_public.chatbox && w(), e(document).trigger("whatsappme:open", [a, wame_public.settings]), i.test(a.link) ? (wame_public.send_event(a.link), t.open(a.link, "whatsappme")) : console.error("WAme: the link doesn't seem safe, it must point to the current domain or whatsapp.com")
                    }
                }
                if (wame_public.store.setItem("whatsappme_visited", "yes"), !wame_public.settings.mobile_only || wame_public.is_mobile) {
                    var b = "whatsappme--show";
                    l || p && n && !wame_public.settings.message_badge && o || (b += " whatsappme--tooltip"), setTimeout(function () {
                        wame_public.$wame.addClass(b)
                    }, s), p && !l && n && (wame_public.settings.message_badge ? i = setTimeout(function () {
                        wame_public.$badge.addClass("whatsappme__badge--in")
                    }, s + n) : o && (i = setTimeout(u, s + n)))
                }
                p && !wame_public.is_mobile && e(".whatsappme__button", wame_public.$wame).mouseenter(function () {
                    wame_public.chatbox || (a = setTimeout(u, 1500))
                }).mouseleave(function () {
                    clearTimeout(a)
                });
                if (e(".whatsappme__button", wame_public.$wame).click(_), e(".whatsappme__close", wame_public.$wame).click(w), e(".whatsappme__box__scroll").on("mousewheel DOMMouseScroll", function (e) {
                    e.preventDefault();
                    var t = e.originalEvent.wheelDelta || -e.originalEvent.detail;
                    this.scrollTop += 30 * (t < 0 ? 1 : -1)
                }), wame_public.is_mobile) {
                    var h, r = t.innerHeight;
                    e(document).on("focus blur", "input, textarea", function () {
                        clearTimeout(h), h = setTimeout(function () {
                            wame_public.$wame.toggleClass("whatsappme--show", .7 * r < t.innerHeight)
                        }, 800)
                    })
                }
                e(document).on("click", ".wame_open", function (e) {
                    e.preventDefault(), wame_public.chatbox || _()
                }), e(document).trigger("whatsappme:start")
            }()
        })
    };

    setTimeout(()=>{
        initWappButton(window.jQuery,window);
    },1000)
});
