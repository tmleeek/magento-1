<!--
/**
 * Webkul_MpDailyDeals Deal list Js
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
    $.widget('mpdeal.dealprolist', {
        _create: function () {
            $('#mpdealselecctall').change(function () {
                if ($(this).is(":checked")) {
                    $('.wk-row-view  .mpcheckbox').each(function () {
                        $(this).prop('checked', true);
                    });
                } else {
                    $('.wk-row-view  .mpcheckbox').each(function () {
                        $(this).prop('checked', false);
                    });
                }
            });

            $('#form-productlist-massdisable').submit(function () {
                if ($('.mpcheckbox:checked').length == 0) {
                    alert('please select product for disable deal.');
                    return false;
                }
            });

            $('.mpcheckbox').change(function () {
                if ($(this).is(":checked")) {
                    var totalCheck = $('.wk-row-view  .mpcheckbox').length,
                        totalCkecked = $('.wk-row-view  .mpcheckbox:checked').length;
                    if (totalCheck == totalCkecked ) {
                        $('#mpdealselecctall').prop('checked', true);
                    }
                } else {
                    $('#mpdealselecctall').prop('checked', false);
                }
            });
        }
    });
    return $.mpdeal.dealprolist;
});