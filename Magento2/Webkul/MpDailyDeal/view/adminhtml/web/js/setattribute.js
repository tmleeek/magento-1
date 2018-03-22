/**
 * Webkul_MpDailyDeal DailyDeals.setAttribute
 * @category  Webkul
 * @package   Webkul_MpDailyDeal
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

 /*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function ($) {
    "use strict";
    $.widget('dailydeals.setattr', {
        _create: function () {
            var attribute = this.options;
            var length = $('input[name="product[deal_from_date]"]').length;
            if (attribute.moduleEnable == 0 || attribute.productType == 'grouped') {
                $('div[data-index="daily-deals"]').hide();
            }
            if ($('select[name="product[deal_status]"]').val() == 0) {
                $('input[name="product[deal_to_date_tmp]"]').attr('disabled', 'disabled');
                $('input[name="product[deal_from_date_tmp]"]').attr('disabled', 'disabled');
                $('input[name="product[deal_value]"]').removeAttr('disabled');
                $('select[name="product[deal_discount_type]"]').removeAttr('disabled');
            }

            $('select[name="product[deal_status]"]').on('change', function (event) {
                if ($(this).val() == 1) {
                    $('input[name="product[deal_to_date_tmp]"]').removeAttr('disabled');
                    $('input[name="product[deal_from_date_tmp]"]').removeAttr('disabled');
                    $('input[name="product[deal_value]"]').removeAttr('disabled');
                    $('select[name="product[deal_discount_type]"]').removeAttr('disabled');
                } else {
                    $('input[name="product[deal_to_date_tmp]"]').attr('disabled', 'disabled');
                    $('input[name="product[deal_from_date_tmp]"]').attr('disabled', 'disabled');
                    $('input[name="product[deal_value]"]').attr('disabled', 'disabled');
                    $('select[name="product[deal_discount_type]"]').attr('disabled', 'disabled');
                }
            });
            if (length > 0) {
                $('input[name="product[deal_discount_percentage]"]').parents('.admin__field').hide();
                $('input[name="product[deal_from_date]"]').parents('.admin__field').hide();
                $('input[name="product[deal_to_date]"]').parents('.admin__field').hide();

                $('input[name="product[deal_to_date_tmp]"]').attr('value', attribute.dealTo).css('width','200px');
                $('input[name="product[deal_from_date_tmp]"]').attr('value', attribute.dealFrom).css('width','200px');

                $('.ui-datepicker-trigger').click(function () {
                    $(this).prev('input').focus();
                });
            } else {
                $('div[data-index="daily-deals"]').on('click', function (event) {
                    $('input[name="product[deal_discount_percentage]"]').parents('.admin__field').hide();
                    $('input[name="product[deal_from_date]"]').parents('.admin__field').hide();
                    $('input[name="product[deal_to_date]"]').parents('.admin__field').hide();

                    $('input[name="product[deal_to_date_tmp]"]').attr('value', attribute.dealTo).css('width','200px');
                    $('input[name="product[deal_from_date_tmp]"]').attr('value', attribute.dealFrom).css('width','200px');
                    $('.ui-datepicker-trigger').click(function () {
                        $(this).prev('input').focus();
                    });
                    $(this).off(event);
                });
            }
        }
        /*_create: function () {
        	var attribute = this.options;
            var length = $('input[name="product[deal_from_date]"]').length;
            if (length > 0) {
            	$('input[name="product[deal_from_date]"]').datetimepicker("destroy");
            	$('input[name="product[deal_to_date]"]').datetimepicker("destroy");
        		var calButt = $('<button />',{"type":"button","class":"ui-datepicker-trigger v-middle"})
                                .append($('<span />').text('Select Date'));
	            $('input[name="product[deal_discount_percentage]"]').parents('.admin__field').hide();
	            $('input[name="product[deal_to_date]"]').datetimepicker({
	                                    'dateFormat':'mm/dd/yy',
	                                    'timeFormat':'HH:mm:ss',
	                                    'minDate': new Date(),
	                                });
	            $('input[name="product[deal_to_date]"]').val(attribute.dealTo).css('width','200px').after(calButt.clone());
	            $('input[name="product[deal_from_date]"]').datetimepicker({
	                                    'dateFormat':'mm/dd/yy',
	                                    'timeFormat':'HH:mm:ss',
	                                    'minDate': new Date(),
	                                });
	            $('input[name="product[deal_from_date]"]').val(attribute.dealFrom).css('width','200px').after(calButt.clone());
	            $('.ui-datepicker-trigger').click(function () {
	                $(this).prev('input').focus(); 
	            });
        	} else {
        		$('div[data-index="daily-deals"]').on('click', function (event) {
        			$('input[name="product[deal_from_date]"]').datetimepicker("destroy");
            	$('input[name="product[deal_to_date]"]').datetimepicker("destroy");
        			var calButt = $('<button />',{"type":"button","class":"ui-datepicker-trigger v-middle"})
                                .append($('<span />').text('Select Date'));
		            $('input[name="product[deal_discount_percentage]"]').parents('.admin__field').hide();
		            $('input[name="product[deal_to_date]"]').datetimepicker({
		                                    'dateFormat':'mm/dd/yy',
		                                    'timeFormat':'HH:mm:ss',
		                                    'minDate': new Date(),
		                                });
		            $('input[name="product[deal_to_date]"]').val(attribute.dealTo).css('width','200px').after(calButt.clone());
		            $('input[name="product[deal_from_date]"]').datetimepicker({
		                                    'dateFormat':'mm/dd/yy',
		                                    'timeFormat':'HH:mm:ss',
		                                    'minDate': new Date(),
		                                });
		            $('input[name="product[deal_from_date]"]').val(attribute.dealFrom).css('width','200px').after(calButt.clone());
		            $('.ui-datepicker-trigger').click(function () {
		                $(this).prev('input').focus(); 
		            });
        			$( this ).off( event );
        		});
        	}    
        }*/
    });
    return $.dailydeals.setattr;
});