<?php

/**
 * ClientTransactionVoid File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Gateway\Http\Client;

/**
 * ClientTransactionVoid Class Doc Comment
 *
 * PHP version 7.0
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class ClientTransactionVoid extends AbstractClientTransaction
{

    /**
     * Process http request
     *
     * @param  array $data
     * @return \Vesta\Result\Error|\Vesta\Result\Successful
     */
    protected function process(array $data)
    {
        return $this->adapter->void($data["PaymentID"], $data["Amount"]);
    }
}
