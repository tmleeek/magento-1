define([
    'uiComponent',
    'ko',
    'jquery',
    'jquery/ui',
    "mage/mage",
    "mage/backend/suggest"
], function (Component, ko, $) {
    'use strict';


    return Component.extend({
        defaults: {
            template: 'Mirasvit_Helpdesk/customer-summary'
        },
        isShowSummary: ko.observable(false),
        isEditCustomerMode: ko.observable(false),
        isEditOrderMode: ko.observable(false),
        customer: ko.observable({}),
        orderId: ko.observable(0),
        emailTo: ko.observable(),
        options: {
            searchField: '[data-field=helpdesk_search_field]',
            searchCustomerField: '[data-field=helpdesk_search_customer]',
        },

        initialize: function () {
            this._super();
            this._bind();
            this._initVars();
        },
        _initVars: function () {
            this.customer(this._customer);

            this.orderId(this._orderId);
            this.emailTo(this._emailTo);
            this.isShowSummary(this.customer().id > 0);
        },
        _bind: function () {
            $(document).on('suggestselect', this.options.searchField, $.proxy(this['onSuggestSelect'], this));
            $(document).on('suggestselect', this.options.searchCustomerField, $.proxy(this['onSuggestSelect2'], this));
        },
        onSuggestSelect: function (e, ui) {
            var customer = ui.item;
            this.customer(customer);
            this.emailTo(customer.email);
            this.isShowSummary(1);
        },
        onSuggestSelect2: function (e, ui) {
            this.customer(ui.item);
            this.isEditCustomerMode(0);
        },
        showEdit: function () {
            this.isEditCustomerMode(1);
        },
        showEditOrder: function () {
            this.isEditOrderMode(1);
        },
        onOrderChange: function (data, e) {
            var orderId = $(e.target).val();
            this.orderId(orderId);
            this.isEditOrderMode(0);
        },
        getOrderById: function (orderId) {
            if (!this.customer().orders.length) {
                return false;
            }
            var newOrder = {}
            $.each(this.customer().orders, function (i, order) {
                if (order.id == orderId) {
                    newOrder = order;
                }
            });
            return newOrder;
        },
        hasOrders: function () {
            return this.customer().orders.length > 1;
        }
    });
});
