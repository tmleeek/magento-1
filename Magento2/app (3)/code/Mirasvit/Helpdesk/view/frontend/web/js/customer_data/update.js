require([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'Mirasvit_Helpdesk/js/view/helpdesk'
], function (Component, customerData) {
    'use strict';

    return Component.extend({
        initialize: function () {
            this._super();

            customerData.reload('helpdesk', true);
        }
    });
});
