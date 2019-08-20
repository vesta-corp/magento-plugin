<?php

/**
 * TransactionDataHandler File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Gateway\Response;

use Vesta\Payment\Gateway\Helper\OrderSubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use \Magento\Framework\Exception\LocalizedException;

/**
 * TransactionDataHandler Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class TransactionDataHandler implements HandlerInterface
{

    const TXN_ID = 'PaymentID';

    /**
     *
     * @var OrderSubjectReader
     */
    private $subjectReader;

    /**
     * TransactionDataHandler constructor.
     *
     * @param OrderSubjectReader $subjectReader
     */
    public function __construct(OrderSubjectReader $subjectReader, \Magento\Framework\Registry $registry)
    {
        $this->subjectReader    = $subjectReader;
        $this->_registry        = $registry;
    }

    /**
     * Handles transaction id
     *
     * @param  array $handlingSubject
     * @param  array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment']) || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface) {
            throw new LocalizedException(__('Payment data object should be provided.'));
        }
        
        $paymentDO = $handlingSubject['payment'];
        $payment = $paymentDO->getPayment();
        $payment->setTransactionId($response['object'][self::TXN_ID]);
        $payment->setIsTransactionClosed(false);
    }
}
