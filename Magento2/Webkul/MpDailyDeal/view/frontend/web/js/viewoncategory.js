<!--
/**
 * Webkul_MpDailyDeals Category View Js
 * @category  Webkul
 * @package   Webkul_MpDailyDeals
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 -->
define([
    "jquery",
    "jquery/ui"
], function ($) {
    "use strict";
    $.widget('auction.categoryview', {
        _create: function () {
            var viewCategoryOpt = this.options;
            var days    = 24*60*60,
            hours   = 60*60,
            minutes = 60;
            $.fn.countdown = function (prop) {
                var options = $.extend({
                    callback    : function () {
alert("");},
                    timestamp   : 0
                },prop);
                var left, d, h, m, s, positions;
                positions = this.find('.position');
                var initialize =  setInterval(function () {
                    left = Math.floor((options.timestamp - (new Date())) / 1000);
                    if (left < 0) {
                        left = 0;
                    }
                    d = Math.floor(left / days);
                    left -= d*days;
                    h = Math.floor(left / hours);
                    left -= h*hours;
                    m = Math.floor(left / minutes);
                    left -= m*minutes;
                    s = left;
                    options.callback(d, h, m, s);
                    if (d==0 && h==0 && m==0 && s==0) {
                        clearInterval(initialize);
                    }
                }, 1000);
                return this;
            };
            $('.deal.wk-daily-deal').each(function () {
                var dealBlock = $(this),
                    colckElm  = dealBlock.find('.wk_cat_count_clock'),
                    timeStamp = new Date(2012, 0, 1),
                    stopTime  = colckElm.attr('data-stoptime'),
                    newYear   = true;
                if ((new Date()) > timeStamp) {
                    timeStamp = colckElm.attr('data-diff-timestamp')*1000;
                    timeStamp = (new Date()).getTime() + timeStamp;
                    newYear = false;
                }
                if (colckElm.length) {
                    colckElm.countdown({
                        timestamp : timeStamp,
                        callback : function (days, hours, minutes, seconds) {
                            var message = "",
                                timez = "",
                                distr = stopTime.split(' '),
                                tzones =  distr[0].split('-'),
                                months = [
                                    'January',
                                    'February',
                                    'March',
                                    'April',
                                    'May',
                                    'June',
                                    'July',
                                    'August',
                                    'September',
                                    'October',
                                    'November',
                                    'December'
                                ];
                            if (hours < 10) {
hours = "0"+hours;}
                            if (minutes < 10) {
minutes = "0"+minutes;}
                            if (seconds < 10) {
seconds = "0"+seconds;}
                            message += "<span class='wk_set_time' title='Days'>"+days + "</span> Days ";
                            message += "<span class='wk_set_time' title='Hours'>"+hours + "</span>:";
                            message += "<span class='wk_set_time' title='Minutes'>"+minutes + "</span>:";
                            message += "<span class='wk_set_time' title='Seconds'>"+seconds + "</span> ";
                            colckElm.html(message);
                            if (hours == 0 && minutes == 0 && seconds == 0) {
                                $.ajax({
                                    url: dealBlock.attr('data-update-url'),
                                    data: {'deal-id':dealBlock.attr('data-deal-id')},
                                    type: 'POST',
                                    dataType:'html',
                                    success: function (transport) {
                                        var response = $.parseJSON(transport);
                                    }
                                });
                                var priceBox = dealBlock.prev('.price-box');
                                priceBox.find('.special-price').remove();
                                priceBox.find('.price-label').remove();
                                dealBlock.remove();
                                priceBox.find('.old-price').addClass('price').removeClass('old-price');
                            }
                        }
                    });
                }
            });
        }
    });
    return $.auction.categoryview;
});