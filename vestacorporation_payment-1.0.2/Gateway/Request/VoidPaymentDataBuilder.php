<?php

/**
 * VoidPaymentDataBuilder File Doc Comment
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
 * VoidPaymentDataBuilder Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class VoidPaymentDataBuilder implements BuilderInterface
{
    use Formatter;

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
        $payment = $paymentDO->getPayment();

        $amount = $payment->getAmountAuthorized();
        $formatAmount = $this->formatPrice($amount);
        return [
            'PaymentID' => $payment->getParentTransactionId() ?: $payment->getLastTransId(),
            'Amount' => $formatAmount,
        ];
    }
}
