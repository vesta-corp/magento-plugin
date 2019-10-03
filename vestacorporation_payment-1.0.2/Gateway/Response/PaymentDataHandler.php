<?php

/**
 * PaymentDataHandler File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Gateway\Response;

use Magento\Payment\Gateway\Helper\ContextHelper;
use Vesta\Payment\Gateway\Helper\OrderSubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * PaymentDataHandler Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class PaymentDataHandler implements HandlerInterface
{

    const TXN_ID = 'PaymentID';
    const AVS_RESPONSE_CODE = 'AcquirerAVSResponseCode';
    const CVV_RESPONSE_CODE = 'AcquirerCVVResponseCode';
    const ACQUIRER_APPROVAL_CODE = 'AcquirerApprovalCode';
    const ACQUIRER_RESPONSE_CODE = 'AcquirerResponseCode';
    const ACQUIRER_RESPONSE_TEXT = 'AcquirerResponseCodeText';
    const CVV = 'cc_cid';
    const GATEWAY_TOKEN = 'gateway_token';
    const PAYMENT_CARD_TOKEN = 'payment_token';
    const VAULT_ENABLED = 'vault_is_enabled';
    const PAYMENT_CC_NUM = 'cc_number';
    const PAYMENT_CC_EXP_MONTH = 'cc_exp_month';
    const PAYMENT_CC_EXP_YEAR = 'cc_exp_year';
    const PAYMENT_CC_TYPE = 'cc_type';

    /**
     *
     * @var OrderSubjectReader
     */
    private $subjectReader;

    /**
     *
     * @var $registry
     */
    private $registry;

    /**
     * Constructor
     *
     * @param OrderSubjectReader $subjectReader
     */
    public function __construct(
        OrderSubjectReader $_subjectReader,
        \Magento\Framework\Registry $_registry
    ) {
        $this->subjectReader = $_subjectReader;
        $this->registry = $_registry;
    }

    /**
     *
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        $paymentCardNum = "";
        $ccNum = $payment->getAdditionalInformation(self::PAYMENT_CC_NUM);
        $paymentCardNum = (strlen($ccNum) > 4) ? substr($ccNum, -4) : $ccNum;
        $payment->setCcTransId($response['object'][self::TXN_ID]);
        $payment->setLastTransId($response['object'][self::TXN_ID]);
        $payment->setCcLast4($paymentCardNum);
        $payment->setCcExpMonth($payment->getAdditionalInformation(self::PAYMENT_CC_EXP_MONTH));
        $payment->setCcExpYear($payment->getAdditionalInformation(self::PAYMENT_CC_EXP_YEAR));
        $ccType = "";
        if ($this->registry->registry(self::PAYMENT_CC_TYPE) != null) {
            $ccType = $this->registry->registry(self::PAYMENT_CC_TYPE);
        } else {
            $ccType = $payment->getAdditionalInformation(self::PAYMENT_CC_TYPE);
        }
        $payment->setCcType($ccType);
        $payment->setAdditionalInformation(self::PAYMENT_CC_TYPE, $ccType);
        $payment->setAdditionalInformation(self::PAYMENT_CC_NUM, $paymentCardNum);
        // remove confidential information from database like cvv, token etc.
        if (isset($response['object'][self::AVS_RESPONSE_CODE])) {
            $payment->setAdditionalInformation(
                self::AVS_RESPONSE_CODE,
                $response['object'][self::AVS_RESPONSE_CODE]
            );
        }
        if (isset($response['object'][self::CVV_RESPONSE_CODE])) {
            $payment->setAdditionalInformation(
                self::CVV_RESPONSE_CODE,
                $response['object'][self::CVV_RESPONSE_CODE]
            );
        }
        if (isset($response['object'][self::ACQUIRER_APPROVAL_CODE])) {
            $payment->setAdditionalInformation(
                self::ACQUIRER_APPROVAL_CODE,
                $response['object'][self::ACQUIRER_APPROVAL_CODE]
            );
        }
        if (isset($response['object'][self::ACQUIRER_RESPONSE_CODE])) {
            $payment->setAdditionalInformation(
                self::ACQUIRER_RESPONSE_CODE,
                $response['object'][self::ACQUIRER_RESPONSE_CODE]
            );
        }
        if (isset($response['object'][self::ACQUIRER_RESPONSE_TEXT])) {
            $payment->setAdditionalInformation(
                self::ACQUIRER_RESPONSE_TEXT,
                $response['object'][self::ACQUIRER_RESPONSE_TEXT]
            );
        }
        if ($payment->hasAdditionalInformation(self::CVV)) {
            $payment->unsAdditionalInformation(self::CVV);
        }
        if ($payment->hasAdditionalInformation(self::GATEWAY_TOKEN)) {
            $payment->unsAdditionalInformation(self::GATEWAY_TOKEN);
        }
        if ($payment->hasAdditionalInformation(self::PAYMENT_CARD_TOKEN)) {
            $payment->unsAdditionalInformation(self::PAYMENT_CARD_TOKEN);
        }
    }
}
