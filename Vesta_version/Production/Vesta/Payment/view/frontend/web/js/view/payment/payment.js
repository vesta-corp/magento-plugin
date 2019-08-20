/**
 * Vesta JS component to render payment template
 *
 * @author Chetu India Team
 */
/*browser:true*/
/*global define*/

define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'vesta_payment',
                component: 'Vesta_Payment/js/view/payment/method-renderer/method'
            }
        );
        return Component.extend({});
    }
);


