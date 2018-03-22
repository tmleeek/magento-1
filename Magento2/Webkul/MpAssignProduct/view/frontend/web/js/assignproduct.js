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
"jquery/ui",
], function ($) {
    'use strict';
    $.widget('mpassignproduct.view', {
        options: {},
        _create: function () {
            var self = this;
            $(document).ready(function () {
                var isConfig = self.options.isConfig;
                var productId = self.options.productId;
                var formKey = self.options.formKey;
                var ajaxUrl = self.options.url;
                var dir = self.options.dir;
                var defaultUrl = self.options.defaultUrl;
                var sortOrder = self.options.sortOrder;
                var btnHtml = self.options.btnHtml;
                var jsonResult = self.options.jsonResult;
                var symbol = self.options.symbol;
                var itemWidth = self.options.itemWidth;
                var superAttribute = {};
                var jsonData = {};
                $(document).on('click', '.wk-ap-add-to-cart', function (event) {
                    if (isConfig == 1) {
                        var associateId = $(this).attr("data-associate-id");
                        $('#product-options-wrapper .super-attribute-select').each(function () {
                            var sId = $(this).attr("id");
                            sId = sId.replace('attribute', '');
                            sId = parseInt(sId);
                            superAttribute[sId] = $(this).val();
                        });
                        jsonData.super_attribute = superAttribute;
                        jsonData.associate_id = associateId;
                    }
                    var assignId = $(this).attr("data-id");
                    var qty = $(this).parent().find(".qty").val();
                    jsonData.mpassignproduct_id = assignId;
                    jsonData.product = productId;
                    jsonData.form_key = formKey;
                    jsonData.qty = qty;
                    $(".wk-loading-mask").removeClass("wk-display-none");
                    $.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        data: jsonData,
                        success: function (data) {
                            if ("backUrl" in data) {
                                location.reload();
                            }
                            $(".wk-loading-mask").addClass("wk-display-none");
                        }
                    });
                });
                $(document).on('change', '#list_sorter', function (event) {
                    var marker = "#wk_list_header";
                    var val = $(this).val();
                    if (val != "rating") {
                        val = "price";
                    }
                    var url = defaultUrl+"?list_order="+val+"&list_dir="+dir+marker;
                    location.href = url;
                });
                $(document).on('click', '#list_dir_asc', function (event) {
                    event.preventDefault();
                    var marker = "#wk_list_header";
                    var dir = "asc";
                    var url = defaultUrl+"?list_order="+sortOrder+"&list_dir="+dir+marker;
                    location.href = url;
                });
                $(document).on('click', '#list_dir_desc', function (event) {
                    event.preventDefault();
                    var marker = "#wk_list_header";
                    var dir = "desc";
                    var url = defaultUrl+"?list_order="+sortOrder+"&list_dir="+dir+marker;
                    location.href = url;
                });
                $('#product-options-wrapper .super-attribute-select').change(function () {
                    resetData(symbol);
                    var flag = 1;
                    setTimeout(function () {
                        $("#product_addtocart_form input[type='hidden']").each(function () {
                            $('#product-options-wrapper .super-attribute-select').each(function () {
                                if ($(this).val() == "") {
                                    flag = 0;
                                }
                            });
                            var name = $(this).attr("name");
                            if (name == "selected_configurable_option") {
                                var productId = $(this).val();
                                if (productId != "" && flag ==1) {
                                    if (typeof jsonResult[productId] != "undefined") {
                                        $(".wk-table-product-list tbody tr").each(function () {
                                            var id = $(this).attr("data-id");
                                            if (typeof jsonResult[productId][id] != "undefined") {
                                                $(this).find(".wk-ap-action-col").html(btnHtml);
                                                $(this).find(".wk-ap-add-to-cart").attr('data-id', id);
                                                $(this).find(".wk-ap-add-to-cart").attr('data-associate-id', jsonResult[productId][id]['id']);
                                                $(this).find(".wk-ap-product-price").html('$'+jsonResult[productId][id]['price']);
                                                var qty = jsonResult[productId][id]['qty'];
                                                if (qty <= 0) {
                                                    var avl = "OUT OF STOCK";
                                                } else {
                                                    var avl = "IN STOCK";
                                                }
                                                $(this).find(".wk-ap-product-avl").html(avl);
                                            }
                                        });
                                    }
                                }
                            }
                        });
                    }, 0);
                });
                $("body").on("click", ".wk-ap-product-showcase-gallery-item img", function () {
                    $(".wk-ap-product-showcase-gallery-item").removeClass("wk-ap-active");
                    $(this).parent().addClass("wk-ap-active");
                    var src = $(this).attr("src");
                    $(".wk-ap-product-showcase-main img").attr("src", src);
                });
                $("body").on("click", ".wk-gallery-right", function () {
                    var currentObject = $(this);
                    var count = $(this).parent().find(".wk-ap-product-showcase-gallery-wrap").attr("data-count");
                    if (count > 5) {
                        var left = $(this).parent().find(".wk-ap-product-showcase-gallery-wrap").css("left");
                        left = left.replace('px', '');
                        left = parseFloat(left);
                        count = count-5;
                        var total = itemWidth*count;
                        var final = left+total;
                        if (final > 0) {
                            $(this).parent().find(".wk-ap-product-showcase-gallery-wrap").animate({ left: '-='+itemWidth+'px' }, 'slow', function () {
                                checkRight(currentObject, itemWidth);
                            });
                        } else {
                            $(this).parent().find(".wk-ap-product-showcase-gallery-wrap").animate({ left: '-'+total+'px' });
                        }
                    }
                });
                $("body").on("click", ".wk-gallery-left", function () {
                    var currentObject = $(this);
                    var left = $(this).parent().find(".wk-ap-product-showcase-gallery-wrap").css("left");
                    left = left.replace('px', '');
                    left = parseFloat(left);
                    if (left >= 0) {
                        $(this).parent().find(".wk-ap-product-showcase-gallery-wrap").animate({ left: '0px' });
                    } else {
                        $(this).parent().find(".wk-ap-product-showcase-gallery-wrap").animate({left: '+='+itemWidth+'px' }, 'slow', function () {
                            checkLeft(currentObject, itemWidth);
                        });
                    }
                });
                $("body").on("click", ".wk-ap-product-image", function () {
                    var display = $(this).parent().find(".wk-ap-product-image-content").css("display");
                    $(".wk-ap-product-image-content").hide();
                    if (display == "none") {
                        $(this).parent().find(".wk-ap-product-image-content").show();
                    }
                    setTimeout(function () {
                        $(".wk-ap-product-image-content").trigger("click");
                    }, 100);
                });
            });
            function resetData(symbol)
            {
                $(".wk-table-product-list tbody tr").each(function () {
                    $(this).find(".wk-ap-action-col").html('');
                    $(this).find(".wk-ap-product-price").html(symbol+'0.00');
                    $(this).find(".wk-ap-product-avl").html("-");
                });
            }

            function checkLeft(currentObject, itemWidth)
            {
                var left = currentObject.parent().find(".wk-ap-product-showcase-gallery-wrap").css("left");
                left = left.replace('px', '');
                left = parseFloat(left);
                if (left >= 0) {
                    currentObject.parent().find(".wk-ap-product-showcase-gallery-wrap").animate({ left: '0px' });
                }
            }

            function checkRight(currentObject, itemWidth)
            {
                var count = currentObject.parent().find(".wk-ap-product-showcase-gallery-wrap").attr("data-count");
                var left = currentObject.parent().find(".wk-ap-product-showcase-gallery-wrap").css("left");
                left = left.replace('px', '');
                left = parseFloat(left);
                count = count-5;
                var total = itemWidth*count;
                var final = left+total;
                if (final <= 0) {
                    currentObject.parent().find(".wk-ap-product-showcase-gallery-wrap").animate({ left: '-'+total+'px' });
                }
            }
        }
    });
    return $.mpassignproduct.view;
});