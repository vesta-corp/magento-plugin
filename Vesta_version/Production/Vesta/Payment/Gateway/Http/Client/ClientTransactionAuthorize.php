<?php

/**
 * ClientTransactionAuthorize File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Gateway\Http\Client;

/**
 * ClientTransactionAuthorize Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class ClientTransactionAuthorize extends AbstractClientTransaction
{

    /**
     *
     * @inheritdoc
     */
    protected function process(array $data)
    {
        return $this->adapter->authorize($data);
    }
}
