<!--
/**
 * Webkul_MpDailyDeals Add Deal On Product Js
 * @category  Webkul
 * @package   Webkul_MpDailyDeals
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 -->
define([
    "jquery",
    "jquery/ui",
    "mage/calendar"
], function ($) {
    "use strict";
    $.widget('auction.categoryview', {
        _create: function () {
            var viewCategoryOpt = this.options;
            $("#deal_from_date").datetimepicker({
                'dateFormat':'mm/dd/yy',
                'timeFormat':'HH:mm:ss',
                'minDate': new Date(),
                onClose: function ( selectedDate ) {
                    $("#deal_to_date").datetimepicker(
                        'option',
                        'minDate',
                        new Date(selectedDate)
                    );
                    $('#deal_to_date').val('');
                }
            });
            
            $("#deal_to_date").datetimepicker({
                                    'dateFormat':'mm/dd/yy',
                                    'timeFormat':'HH:mm:ss',
                                    'minDate': new Date(),
                                    onClose: function ( selectedDate ) {
                                        var from = $('#deal_from_date').datetimepicker("getDate");
                                        var to = $('#deal_to_date').datetimepicker("getDate");
                                        if (from == null || from > to) {
                                            alert('you can not select previous date from deal start date');
                                        }
                                    }
                                });

            $("#deal_status").change(function () {
                if ($(this).val()==0) {
                    $(".wk-mp-fieldset .control input").each(function () {
                        $(this).removeClass('required-entry');
                        $(this).parents('.field').removeClass('required');
                    });
                } else {
                    $(".wk-mp-fieldset .control input").each(function () {
                        $(this).addClass('required-entry');
                        $(this).parents('.field').addClass('required');
                    });
                }
            });
        }
    });
    return $.auction.categoryview;
});