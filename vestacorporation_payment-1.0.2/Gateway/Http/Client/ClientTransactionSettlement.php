<?php

/**
 * ClientTransactionSettlement File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Gateway\Http\Client;

use Vesta\Payment\Gateway\Request\PaymentCaptureDataBuilder;

/**
 * ClientTransactionSettlement Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class ClientTransactionSettlement extends AbstractClientTransaction
{

    /**
     *
     * @inheritdoc
     */
    protected function process(array $data)
    {

        if (null !== PaymentCaptureDataBuilder::TRANSACTION_TYPE &&
            $data[PaymentCaptureDataBuilder::TRANSACTION_TYPE] == 'sale') {
            unset($data[PaymentCaptureDataBuilder::TRANSACTION_TYPE]);
            return $this->adapter->sale($data);
        } else {
            unset($data[PaymentCaptureDataBuilder::TRANSACTION_TYPE]);
            return $this->adapter->cccomplete(
                $data[PaymentCaptureDataBuilder::TRANSACTION_ID],
                $data[PaymentCaptureDataBuilder::AMOUNT]
            );
        }
    }
}
