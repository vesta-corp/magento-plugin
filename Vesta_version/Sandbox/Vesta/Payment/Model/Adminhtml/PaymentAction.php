<?php

/**
 * PaymentAction File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Model\Adminhtml;

use Magento\Payment\Model\Method\AbstractMethod;

/**
 * PaymentAction Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class PaymentAction implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Get payment actions
     *
     * @return array action array
     */
    public function toOptionArray()
    {

        return [
            [
                'value' => AbstractMethod::ACTION_AUTHORIZE,
                'label' => __('Authorize Only')
            ],
            [
                'value' => AbstractMethod::ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Authorize & Capture')
            ]
        ];
    }
}
