<?php

/**
 * PaymentDetailsBuilder File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Gateway\Request;

use Vesta\Payment\Gateway\Config\PaymentConfig;
use Vesta\Payment\Observer\PaymentDataAssignObserver;
use Vesta\Payment\Gateway\Helper\OrderSubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * PaymentDetailsBuilder Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class PaymentDetailsBuilder implements BuilderInterface
{

    use Formatter;

    /**
     *
     * @var PaymentConfig
     */
    private $config;
    
    /**
     *
     * @var OrderSubjectReader
     */
    private $subjectReader;
    
    /**
     * Web session
     *
     * @var mixed
     */
    public $webSession;

    /**
     * Constructor
     *
     * @param PaymentConfig $config
     * @param OrderSubjectReader $subjectReader
     */
    public function __construct(
        PaymentConfig $config,
        OrderSubjectReader $subjectReader,
        SessionManagerInterface $session
    ) {
        $this->config           = $config;
        $this->subjectReader    = $subjectReader;
        $this->webSession = $session;
    }

    /**
     *
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $this->webSession->start();
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();
        $vaultValue = $payment->getAdditionalInformation(
            PaymentDataAssignObserver::PAYMENT_VAULT_ENABLED
        );
        $conditionalArr = [];
        $cardNum = $payment->getAdditionalInformation(
            PaymentDataAssignObserver::PAYMENT_CC_NUM
        );
        $paymentToken = $payment->getAdditionalInformation(
            PaymentDataAssignObserver::PAYMENT_CARD_TOKEN
        );
        if (null == $payment->getAdditionalInformation(PaymentDataAssignObserver::PAYMENT_TOKEN_NUM)) {
            $accountIndicator = ($paymentToken == null) ? "1" : "2";
            $accountNum = ($paymentToken == null) ? $cardNum : $paymentToken;
            $conditionalArr = [
                "AccountNumberIndicator" => $accountIndicator,
                "AccountNumber" => $accountNum
            ];

            if ($vaultValue == 1) {
                $conditionalArr = array_merge($conditionalArr, ["TransactionType" => 2]);
            }
        } else {
            $accountNum = $payment->getAdditionalInformation(
                PaymentDataAssignObserver::PAYMENT_TOKEN_NUM
            );
            $conditionalArr = [
                "AccountNumberIndicator" => 3,
                "AccountNumber" => $accountNum,
                "TransactionType" => 5,
            ];
        }

        $paramsArr = [
            'Amount' => $this->formatPrice(
                $this->subjectReader->readAmount($buildSubject)
            ),
            "CVV" => $payment->getAdditionalInformation(
                PaymentDataAssignObserver::PAYMENT_CVV
            ),
            "ExpirationMM" => $payment->getAdditionalInformation(
                PaymentDataAssignObserver::PAYMENT_CC_EXP_MONTH
            ),
            "ExpirationYY" => $payment->getAdditionalInformation(
                PaymentDataAssignObserver::PAYMENT_CC_EXP_YEAR
            ),
            "WebSessionID" => $this->webSession->getWebSessionID(),
            "TransactionID" => $this->webSession->getTransId(),
            "PaymentSource" => "WEB",
        ];

        return array_merge($paramsArr, $conditionalArr);
    }
}
