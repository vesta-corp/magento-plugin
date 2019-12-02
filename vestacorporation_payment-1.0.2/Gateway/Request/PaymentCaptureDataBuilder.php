<?php

/**
 * PaymentCaptureDataBuilder File Doc Comment
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

/**
 * PaymentCaptureDataBuilder Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class PaymentCaptureDataBuilder implements BuilderInterface
{
    use Formatter;
    const TRANSACTION_ID = 'PaymentID';
    const AMOUNT = 'Amount';
    const TRANSACTION_TYPE = 'transaction_type';

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
     *
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();

        $transactionArr = explode("-", $payment->getTransactionId());
        $transactionId = $transactionArr[0];
        
        if (!$transactionId) {
            return [
                self::TRANSACTION_TYPE => 'sale',
                self::AMOUNT => $this->formatPrice($this->subjectReader->readAmount($buildSubject))
            ];
        }

        return [
            self::TRANSACTION_TYPE => 'capture',
            self::TRANSACTION_ID => $transactionId,
            self::AMOUNT => $this->formatPrice($this->subjectReader->readAmount($buildSubject))
        ];
    }
}
