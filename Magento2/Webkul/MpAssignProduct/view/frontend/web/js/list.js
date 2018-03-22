/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
"jquery",
'Magento_Ui/js/modal/confirm',
'Magento_Ui/js/modal/alert',
"jquery/ui",
], function ($, confirmation, alert) {
    'use strict';
    $.widget('mpassignproduct.list', {
        options: {},
        _create: function () {
            var self = this;
            $(document).ready(function () {
                var isConfig = self.options.isConfig;
                var editTitle = self.options.editTitle;
                var editAction = self.options.editAction;
                var deleteTitle = self.options.deleteTitle;
                var deleteAction = self.options.deleteAction;
                var msg = self.options.msg;
                $(document).on('click', '.wk-ap-edit-item', function (event) {
                    var assignId = $(this).attr("data-id");
                    confirmation({
                        title: 'Confirmation',
                        content: "<div class='wk-ap-warning-content'>"+editTitle+"</div>",
                        actions: {
                            confirm: function () {
                                var url = editAction;
                                window.location.href = url+"id/"+assignId;
                            },
                            cancel: function (){},
                            always: function (){}
                        }
                    });
                });
                $(document).on('click', '.wk-ap-delete-item', function (event) {
                    var assignId = $(this).attr("data-id");
                    confirmation({
                        title: 'Confirmation',
                        content: "<div class='wk-ap-warning-content'>"+deleteTitle+"</div>",
                        actions: {
                            confirm: function () {
                                var url = deleteAction;
                                window.location.href = url+"id/"+assignId;
                            },
                            cancel: function (){},
                            always: function (){}
                        }
                    });
                });
                $(document).on('click', '.wk-ap-del', function (event) {
                    var flag = 0;
                    $(".wk-ap-del-chkbox").each(function () {
                        if ($(this).is(':checked')) {
                            flag = 1;
                        }
                    });
                    if (flag == 0) {
                        alert({
                            title: 'Warning',
                            content: "<div class='wk-ap-warning-content'>"+msg+"</div>",
                            actions: {
                                always: function (){}
                            }
                        });
                        return false;
                    }
                });
            });
        }
    });
    return $.mpassignproduct.list;
});