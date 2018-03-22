define([
    "jquery",
    'underscore',
    'uiCollection',
    'Magento_Ui/js/lib/spinner',
    "domReady!",
], function ($, _,  Collection, loader) {
    'use strict';


    return Collection.extend({
        initialize: function () {
            this._super()
                .hideLoader();
            return this;
        },
        hideLoader: function () {
            loader.hide();


            return this;
        },
        /**
         * Shows loader.
         */
        showLoader: function () {
            loader.show();
        }
    });



    //jQuery(document).ready(function ($) {
    //    var el   = $('#reply');
    //    var f    = $('#is_internal');
    //    var note = $('#reply_note');
    //
    //    //var updateSaveBtn = function () {
    //    //    if ($('#reply').val() == '') {
    //    //        $('.saveTicketBtn').html($.mage.translate.translate('<span>Update</span>'));
    //    //        $('.saveAndContinueTicketBtn').html($.mage.translate.translate('<span>Update And Continue Edit</span>'));
    //    //    } else {
    //    //        $('.saveTicketBtn').html($.mage.translate.translate('<span> Send </span>'));
    //    //        $('.saveAndContinueTicketBtn').html($.mage.translate.translate('<span> Send And Continue Edit </span>'));
    //    //    }
    //    //}
    //    //
    //    //$('#third_party_email').parent().parent().hide();
    //    //$('#third_party_email').removeClass('required-entry');
    //    //$('#reply_type').change(function() {
    //    //    var type = $('#reply_type').val();
    //    //    var email = $('#third_party_email').parent().parent();
    //    //    var emailInput = $('#third_party_email');
    //    //    el.removeClass('internal');
    //    //    if (type == 'public') {
    //    //        note.html('');
    //    //        email.hide();
    //    //        emailInput.removeClass('required-entry');
    //    //    } else if (type == 'internal') {
    //    //        el.addClass('internal');
    //    //        note.html($.mage.translate.translate('Only helpdesk staff will see this message'));
    //    //        email.hide();
    //    //        emailInput.removeClass('required-entry');
    //    //    } else if (type == 'public_third') {
    //    //        note.html($.mage.translate.translate('Your message will be emailed to the third party.<br> Customer will see it and all third party replies.'));
    //    //        email.show();
    //    //        emailInput.addClass('required-entry');
    //    //    } else if (type == 'internal_third') {
    //    //        el.addClass('internal');
    //    //        note.html($.mage.translate.translate('Your message will be emailed to the third party. <br>Customer will NOT see it and all third party replies.'));
    //    //        email.show();
    //    //        emailInput.addClass('required-entry');
    //    //    }
    //    //});
    //    //$('#public_reply_btn').click(function() {
    //    //    el.removeClass('internal');
    //    //    $('#public_reply_btn').addClass('active');
    //    //    $('#internal_reply_btn').removeClass('active');
    //    //    f.val(0);
    //    //    note.html('');
    //    //    updateSaveBtn();
    //    //});
    //    //
    //    //$('#internal_reply_btn').click(function() {
    //    //    el.addClass('internal');
    //    //    $('#public_reply_btn').removeClass('active');
    //    //    $('#internal_reply_btn').addClass('active');
    //    //    f.val(1);
    //    //    note.html($.mage.translate.translate('Only helpdesk staff will see this message'));
    //    //    updateSaveBtn();
    //    //});
    //    //
    //    //$('#reply').keyup(function() {
    //    //    updateSaveBtn();
    //    //});
    //    //var searchResults;
    //    //var fillOrders = function() {
    //    //    $('#view_customer_link').hide();
    //    //    $('#view_order_link').hide();
    //    //    var customer_id = $('#customer_id').val();
    //    //    $('#order_id').empty();
    //    //    if (customer_id !== 0) {
    //    //        $.each(searchResults, function (index, value) {
    //    //            if (value['id'] == customer_id) {
    //    //                $.each(value['orders'], function(index, value) {
    //    //                    var id = value['id'];
    //    //                    var text = value['name'];
    //    //                    $('#order_id').append(
    //    //                        $('<option></option>').val(id).html(text)
    //    //                    );
    //    //                });
    //    //                if ($('#ticket_id').length == 0) {
    //    //                    $('#customer_email').val(value['email']);
    //    //                }
    //    //            }
    //    //        });
    //    //
    //    //    }
    //    //    $('#order_id').show();
    //    //
    //    //}
    //    //
    //    //$('#find-customer-btn').click(function() {
    //    //    var url = $('#find-customer-btn').attr('data-url') + '?q=' +$('#customer_query').val();
    //    //    $.ajax({
    //    //        url: url,
    //    //        dataType: 'json',
    //    //        data: {
    //    //            form_key: FORM_KEY
    //    //        },
    //    //        success: function(data)
    //    //        {
    //    //            $('#customer_id').empty();
    //    //            searchResults = data;
    //    //            $.each(data, function (index, text) {
    //    //                $('#customer_id').append(
    //    //                    $('<option></option>').val(text['id']).html(text['name'])
    //    //                );
    //    //                $('#customer_id').show();
    //    //            });
    //    //            fillOrders();
    //    //        },
    //    //        context: $('body'),
    //    //        showLoader: true
    //    //    });
    //    //    return false;
    //    //});
    //    //$('#customer_id').change(fillOrders);
    //    //$('#order_id').change(function() {
    //    //    $('#view_order_link').hide();
    //    //});
    //    //$('#template_id').change(function() {
    //    //    var id = $('#template_id').val();
    //    //    if (id != 0) {
    //    //        var template = $('#htmltemplate-' + id).text();
    //    //        var val = $('#reply').val();
    //    //        if (val != '') {
    //    //            val = val + '\n';
    //    //        }
    //    //        $('#reply').val(val + template);
    //    //        $('#template_id').val(0);
    //    //        updateSaveBtn();
    //    //    }
    //    //});
    //
    //    /***  FOLLOW UP ***/
    //    var period_date = $('#fp_execute_at').parent().parent();
    //    var period_value = $('#fp_period_value').parent().parent();
    //    var periodInit = function () {
    //        var unit = $('#fp_period_unit').val();
    //        if (unit == 'custom') {
    //            period_value.hide();
    //            period_date.show();
    //        } else {
    //            period_value.show();
    //            period_date.hide();
    //        }
    //    }
    //    periodInit();
    //    $('#fp_period_unit').bind('change', periodInit);
    //
    //    var remind_email = $('#fp_remind_email').parent().parent();
    //    var remindInit = function () {
    //        var state = $('#fp_is_remind').is(':checked');
    //        if (state == 1) {
    //            remind_email.show();
    //        } else {
    //            remind_email.hide();
    //        }
    //    };
    //    remindInit();
    //    $('#fp_is_remind').bind('change', remindInit);
    //
    //    /*** end FOLLOW UP ***/
    //
    //    /*** CC, BCC ***/
    //
    //    //var allowCC = function() {
    //    //    $('#email_add_cc').hide();
    //    //    $('.field-cc').show();
    //    //    return false;
    //    //}
    //    //var allowBCC = function() {
    //    //    $('#email_add_bcc').hide();
    //    //    $('.field-bcc').show();
    //    //    return false;
    //    //}
    //    //if ($('#cc').val() == '') {
    //    //    $('.field-cc').hide();
    //    //} else {
    //    //    $('#email_add_cc').hide();
    //    //}
    //    //if ($('#bcc').val() == '') {
    //    //    $('.field-bcc').hide();
    //    //} else {
    //    //    $('#email_add_bcc').hide();
    //    //}
    //    //
    //    //$('#email_add_cc').bind('click', allowCC);
    //    //$('#email_add_bcc').bind('click', allowBCC);
    //
    //    /*** end CC, BCC ***/
    //});


});