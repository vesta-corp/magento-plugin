<?php

/**
 * VoidDataHandler File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Gateway\Response;

use Magento\Sales\Model\Order\Payment;

/**
 * VoidDataHandler Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class VoidDataHandler extends TransactionDataHandler
{

    /**
     *
     * @param  Payment $orderPayment
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function setTransactionId(Payment $orderPayment)
    {
        return;
    }

    /**
     * Whether transaction should be closed
     *
     * @return bool
     */
    protected function shouldCloseTransaction()
    {
        return true;
    }

    /**
     * Whether parent transaction should be closed
     *
     * @param Payment $orderPayment
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function shouldCloseParentTransaction(Payment $orderPayment)
    {
        return true;
    }
}
