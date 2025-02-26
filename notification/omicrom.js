jQuery.curCSS = function (element, prop, val) {
    return jQuery(element).css(prop, val);
};

$(document).ready(function () {
    var myNotifications = {
        alerts: '#alerts',
        pipes: '#pipes',
        messages: '#messages'
    };
    var notifications = new $.ttwNotificationMenu({
        notificationList: {
            anchor: 'bubble',
            offset: '0 15'
        }
    });
    notifications.initMenu(myNotifications);
    function callNotifications() {
        $.each(myNotifications, function (i, obj) {

            $.ajax(
                    'ajax_xml_notifications.php?key=' + i,
                    {
                        success: function (response) {
                            var jsonData = JSON.parse(response);
                            $.each(jsonData, function (j, obj2) {
                                notifications.createNotification(obj2);
                            });     
                        },
                        error: function (response) {
                            console.log("Ocurrio un error: " + response);
                        }
                    }
            );
            if(i === "alerts"){
                isAlerts(notifications.getNotifications(i, "all"));
            }
        });
    }
    function isAlerts(notifications) {
        $.each(notifications, function (i, obj) {
            if (obj.category === "alerts") {
                $("#img_alerts").attr("src", "libnvo/alerta.gif");
            }
        });
    }

    window.setInterval(function () {
        callNotifications();
    }, 15000);

});

/*!
 * jQuery Tools v1.2.5 - The missing UI library for the Web
 *
 * tooltip/tooltip.js
 * tooltip/tooltip.slide.js
 *
 * NO COPYRIGHTS OR LICENSES. DO WHAT YOU LIKE.
 *
 * http://flowplayer.org/tools/
 *
 */
(function (a) {
    a.tools = a.tools || {version: "v1.2.5"}, a.tools.tooltip = {conf: {effect: "toggle", fadeOutSpeed: "fast", predelay: 0, delay: 30, opacity: 1, tip: 0, position: ["top", "center"], offset: [0, 0], relative: !1, cancelDefault: !0, events: {def: "mouseenter,mouseleave", input: "focus,blur", widget: "focus mouseenter,blur mouseleave", tooltip: "mouseenter,mouseleave"}, layout: "<div/>", tipClass: "tooltip"}, addEffect: function (a, c, d) {
            b[a] = [c, d]
        }};
    var b = {toggle: [function (a) {
                var b = this.getConf(), c = this.getTip(), d = b.opacity;
                d < 1 && c.css({opacity: d}), c.show(), a.call()
            }, function (a) {
                this.getTip().hide(), a.call()
            }], fade: [function (a) {
                var b = this.getConf();
                this.getTip().fadeTo(b.fadeInSpeed, b.opacity, a)
            }, function (a) {
                this.getTip().fadeOut(this.getConf().fadeOutSpeed, a)
            }]};
    function c(b, c, d) {
        var e = d.relative ? b.position().top : b.offset().top, f = d.relative ? b.position().left : b.offset().left, g = d.position[0];
        e -= c.outerHeight() - d.offset[0], f += b.outerWidth() + d.offset[1], /iPad/i.test(navigator.userAgent) && (e -= a(window).scrollTop());
        var h = c.outerHeight() + b.outerHeight();
        g == "center" && (e += h / 2), g == "bottom" && (e += h), g = d.position[1];
        var i = c.outerWidth() + b.outerWidth();
        g == "center" && (f -= i / 2), g == "left" && (f -= i);
        return{top: e, left: f}
    }
    function d(d, e) {
        var f = this, g = d.add(f), h, i = 0, j = 0, k = d.attr("title"), l = d.attr("data-tooltip"), m = b[e.effect], n, o = d.is(":input"), p = o && d.is(":checkbox, :radio, select, :button, :submit"), q = d.attr("type"), r = e.events[q] || e.events[o ? p ? "widget" : "input" : "def"];
        if (!m)
            throw"Nonexistent effect \"" + e.effect + "\"";
        r = r.split(/,\s*/);
        if (r.length != 2)
            throw"Tooltip: bad events configuration for " + q;
        d.bind(r[0], function (a) {
            clearTimeout(i), e.predelay ? j = setTimeout(function () {
                f.show(a)
            }, e.predelay) : f.show(a)
        }).bind(r[1], function (a) {
            clearTimeout(j), e.delay ? i = setTimeout(function () {
                f.hide(a)
            }, e.delay) : f.hide(a)
        }), k && e.cancelDefault && (d.removeAttr("title"), d.data("title", k)), a.extend(f, {show: function (b) {
                if (!h) {
                    l ? h = a(l) : e.tip ? h = a(e.tip).eq(0) : k ? h = a(e.layout).addClass(e.tipClass).appendTo(document.body).hide().append(k) : (h = d.next(), h.length || (h = d.parent().next()));
                    if (!h.length)
                        throw"Cannot find tooltip for " + d
                }
                if (f.isShown())
                    return f;
                h.stop(!0, !0);
                var o = c(d, h, e);
                e.tip && h.html(d.data("title")), b = b || a.Event(), b.type = "onBeforeShow", g.trigger(b, [o]);
                if (b.isDefaultPrevented())
                    return f;
                o = c(d, h, e), h.css({position: "absolute", top: o.top, left: o.left}), n = !0, m[0].call(f, function () {
                    b.type = "onShow", n = "full", g.trigger(b)
                });
                var p = e.events.tooltip.split(/,\s*/);
                h.data("__set") || (h.bind(p[0], function () {
                    clearTimeout(i), clearTimeout(j)
                }), p[1] && !d.is("input:not(:checkbox, :radio), textarea") && h.bind(p[1], function (a) {
                    a.relatedTarget != d[0] && d.trigger(r[1].split(" ")[0])
                }), h.data("__set", !0));
                return f
            }, hide: function (c) {
                if (!h || !f.isShown())
                    return f;
                c = c || a.Event(), c.type = "onBeforeHide", g.trigger(c);
                if (!c.isDefaultPrevented()) {
                    n = !1, b[e.effect][1].call(f, function () {
                        c.type = "onHide", g.trigger(c)
                    });
                    return f
                }
            }, isShown: function (a) {
                return a ? n == "full" : n
            }, getConf: function () {
                return e
            }, getTip: function () {
                return h
            }, getTrigger: function () {
                return d
            }}), a.each("onHide,onBeforeShow,onShow,onBeforeHide".split(","), function (b, c) {
            a.isFunction(e[c]) && a(f).bind(c, e[c]), f[c] = function (b) {
                b && a(f).bind(c, b);
                return f
            }
        })
    }
    a.fn.tooltip = function (b) {
        var c = this.data("tooltip");
        if (c)
            return c;
        b = a.extend(!0, {}, a.tools.tooltip.conf, b), typeof b.position == "string" && (b.position = b.position.split(/,?\s/)), this.each(function () {
            c = new d(a(this), b), a(this).data("tooltip", c)
        });
        return b.api ? c : this
    }
})(jQuery);
(function (a) {
    var b = a.tools.tooltip;
    a.extend(b.conf, {direction: "up", bounce: !1, slideOffset: 10, slideInSpeed: 200, slideOutSpeed: 200, slideFade: true});
    var c = {up: ["-", "top"], down: ["+", "top"], left: ["-", "left"], right: ["+", "left"]};
    b.addEffect("slide", function (a) {
        var b = this.getConf(), d = this.getTip(), e = b.slideFade ? {opacity: b.opacity} : {}, f = c[b.direction] || c.up;
        e[f[1]] = f[0] + "=" + b.slideOffset, b.slideFade && d.css({opacity: 0}), d.show().animate(e, b.slideInSpeed, a)
    }, function (b) {
        var d = this.getConf(), e = d.slideOffset, f = d.slideFade ? {opacity: 0} : {}, g = c[d.direction] || c.up, h = "" + g[0];
        d.bounce && (h = h == "+" ? "-" : "+"), f[g[1]] = h + "=" + e, this.getTip().animate(f, d.slideOutSpeed, function () {
            a(this).hide(), b.call()
        })
    })
})(jQuery);
