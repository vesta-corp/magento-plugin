<?php

/**
 * ClientTransactionRefund File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Gateway\Http\Client;

use Vesta\Payment\Gateway\Request\PaymentRefundDataBuilder;

/**
 * ClientTransactionRefund Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class ClientTransactionRefund extends AbstractClientTransaction
{

    /**
     * Process http request
     *
     * @param  array $data
     * @return array request data
     */
    protected function process(array $data)
    {
        return $this->adapter->ccreturn(
            $data[PaymentRefundDataBuilder::TRANSACTION_ID],
            $data[PaymentRefundDataBuilder::AMOUNT]
        );
    }
}
