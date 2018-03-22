define([
    'ko',
    'underscore',
    'jquery',
    'Mirasvit_Report/js/lib/daterangepicker/daterangepicker'
], function (ko, _, $) {
    'use strict';

    var defaults = {
        "autoApply": true,
        "showWeekNumbers": true,
        "showDropdowns": true,
        "template": '<div class="daterangepicker dropdown-menu">' +
        '<div class="calendar left">' +
        '<div class="daterangepicker_input">' +
        '<input class="admin__control-text" type="text" name="daterangepicker_start" value="" />' +
        '<div class="calendar-time">' +
        '<div></div>' +
        '</div>' +
        '</div>' +
        '<div class="calendar-table"></div>' +
        '</div>' +
        '<div class="calendar right">' +
        '<div class="daterangepicker_input">' +
        '<input class="admin__control-text" type="text" name="daterangepicker_end" value="" />' +
        '<div class="calendar-time">' +
        '<div></div>' +
        '</div>' +
        '</div>' +
        '<div class="calendar-table"></div>' +
        '</div>' +
        '<div class="ranges">' +
        '</div>' +
        '</div>'
    };

    ko.bindingHandlers.daterangepicker = {
        init: function (el, valueAccessor) {
            var config = valueAccessor(),
                observableFrom,
                observableTo,
                options = {};

            _.extend(options, defaults);

            if (typeof config === 'object') {
                observableFrom = config.storageFrom;
                observableTo = config.storageTo;

                _.extend(options, config.options);
            }

            $(el).daterangepicker(
                options,
                function (start, end, label) {
                    observableFrom(start.format('YYYY-MM-DD'));
                    observableTo(end.format('YYYY-MM-DD'));
                }
            );
        },

        /**
         * Reads target observable from valueAccessor and writes its' value to el.value
         * @param {HTMLElement} el - Element, that binding is applied to
         * @param {Function} valueAccessor - Function that returns value, passed to binding
         */
        update: function (el, valueAccessor) {
            var config = valueAccessor(),
                observable,
                value;

            observable = typeof config === 'object' ?
                config.storage :
                config;

            //value = observable();
            //
            //value ?
            //    $(el).datepicker('setDate', value) :
            //    (el.value = '');
        }
    }
})
;