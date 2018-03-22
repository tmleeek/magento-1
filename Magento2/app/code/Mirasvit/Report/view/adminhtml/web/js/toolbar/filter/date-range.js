define([
    'underscore',
    'ko',
    'uiElement',
    'Mirasvit_Report/js/lib/ko/bind/daterangepicker'
], function (_, ko, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            template: 'report/toolbar/filter/date-range',

            exports: {
                from: '${ $.provider }:params.filters[${ $.column }].from',
                to: '${ $.provider }:params.filters[${ $.column }].to'
            },

            listens: {}
        },

        initialize: function () {
            this._super();

            return this;
        },

        initObservable: function () {
            this._super();

            this.from = ko.observable(this.value.from);
            this.to = ko.observable(this.value.to);

            return this;
        }
    });
});
