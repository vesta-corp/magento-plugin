/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/lib/view/utils/dom-observer',
    'mage/translate',
], function ($, Class, alert, domObserver, $t) {
    'use strict';

    return Class.extend({

        defaults: {
            $selector: null,
            selector: 'edit_form',
            container: 'payment_form_vesta_payment',
            active: false,
            scriptLoaded: false,
            vesta_payment: null,
            selectedCardType: null,
            imports: {
                onActiveChange: 'active'
            }
        },

        initObservable: function () {

            var self = this;
            self.$selector = $('#' + self.selector);
            this._super()
                .observe([
                    'active',
                    'scriptLoaded',
                    'selectedCardType'
                ]);
            return this;
        },
        changePaymentMethod: function (event, method) {
            this.active(method === this.code);
            return this;
        },
        onActiveChange: function (isActive) {
            console.log(this.submitOrder);
            if (!isActive) {
                return;
            }
            window.order.addExcludedPaymentMethod(this.code);
        },
        setPaymentDetails: function () {
            console.log("payment details set");
        },
    });
});
