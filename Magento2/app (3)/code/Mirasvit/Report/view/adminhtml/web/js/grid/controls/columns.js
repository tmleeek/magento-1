define([
    'underscore',
    'mageUtils',
    'mage/translate',
    'Magento_Ui/js/grid/controls/columns'
], function (_, utils, $t, Columns) {
    'use strict';

    return Columns.extend({
        defaults: {
            exports: {
                columns: '${ $.provider }:params.columns'
            }
        },

        /**
         * Counts number of visible columns.
         *
         * @returns {Number}
         */
        countVisible: function () {
            var columns = [];
            _.each(this.elems.filter('visible'), function (item) {
                columns.push(item.index);
            });

            if (this.get('columns') == undefined || columns.length > this.get('columns').length) {
                // set and reload
                this.set('columns', columns);
            }

            return this.elems.filter('visible').length;
        }
    });
});
