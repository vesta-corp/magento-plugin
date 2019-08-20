<?php

/**
 * PaymentRefundDataBuilder File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Gateway\Request;

use Vesta\Payment\Gateway\Helper\OrderSubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * PaymentRefundDataBuilder Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class PaymentRefundDataBuilder implements BuilderInterface
{

    use Formatter;

    const TRANSACTION_ID = 'PaymentID';
    const AMOUNT = 'Amount';

    /**
     *
     * @var OrderSubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param OrderSubjectReader $subjectReader
     */
    public function __construct(OrderSubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Builds ENV request
     *
     * @param  array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /**
         *
         *
         * @var Payment $payment
         */
        $payment = $paymentDO->getPayment();

        $amount = null;
        try {
            $amount = $this->formatPrice($this->subjectReader->readAmount($buildSubject));
        } catch (\InvalidArgumentException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }

        $txnId = str_replace(
            '-' . TransactionInterface::TYPE_CAPTURE,
            '',
            $payment->getParentTransactionId()
        );

        return [
            self::TRANSACTION_ID => $txnId,
            self::AMOUNT => $amount
        ];
    }
}
