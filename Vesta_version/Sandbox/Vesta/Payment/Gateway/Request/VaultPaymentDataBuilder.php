<?php

/**
 * VaultPaymentDataBuilder File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Vesta\Payment\Gateway\Helper\OrderSubjectReader;
use Vesta\Payment\Observer\PaymentDataAssignObserver;

/**
 * VaultPaymentDataBuilder Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class VaultPaymentDataBuilder implements BuilderInterface
{

    /**
     * The option that determines whether the payment method associated with
     * the successful transaction should be stored in the Vault.
     */
    const STORE_CARD = 'StoreCard';
    /**
     *
     * @var OrderSubjectReader
     */
    private $subjectReader;

    /**
     * constructor function
     *
     * @param OrderSubjectReader $subjectReader
     */
    public function __construct(OrderSubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $vault = $payment->getAdditionalInformation(PaymentDataAssignObserver::PAYMENT_VAULT_ENABLED) ?
            $payment->getAdditionalInformation(PaymentDataAssignObserver::PAYMENT_VAULT_ENABLED) : 0;
        return [
           self::STORE_CARD => $vault
        ];
    }
}
