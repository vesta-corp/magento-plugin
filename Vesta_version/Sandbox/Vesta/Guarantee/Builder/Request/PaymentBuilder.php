<?php

/**
 * Vesta Guarantee Recent order payment data.
 *
 * @author Chetu Team.
 */

namespace Vesta\Guarantee\Builder\Request;

use Vesta\Guarantee\Helper\ConfigHelper;

/**
 * Vesta Guarantee Recent order payment data parameters.
 *
 * @author Chetu Team.
 */
class PaymentBuilder
{

    /**
     * predefined constants parameters
     */
    const STORE_CARD = 0;
    const ACCOUNT_INDICATOR = 4;
    const PAYMENT_SOURCE = "WEB";
    const PAYMENT_DESCRIPTOR = "Magento order";
    const PROCESSER_AUTH_CODE = "acquirer_auth_res_code";
    const PROCESSER_AVS_CODE = "acquirer_avs_res_code";
    const PROCESSER_CVV_CODE = "acquirer_cvv_res_code";

    /**
     * Order data.
     *
     * @var mixed
     */
    private $order;

    /**
     * Configuration object
     *
     * @var Object
     */
    private $configHelper;

    public function __construct(ConfigHelper $config)
    {
        $this->configHelper = $config;
    }
    /**
     * Build API Risk parameters.
     *
     * @param Object $order
     *
     * @return array parameters
     */

    public function build($order = null)
    {
        $this->order = $order;
        return $this->getParams();
    }

    /**
     * Get payment information parameters
     *
     * @return Array parameters
     */
    private function getParams()
    {
        $this->getPaymentVerificationData();
        $payment = $this->order->getPayment();
        $expirationMMYY = $this->prepareExpMonthYearMonth($payment->getCcExpMonth(), $payment->getCcExpYear());
        $paymentData = [
            "AccountNumber" => $payment->getCcLast4(),
            "AccountNumberIndicator" => self::ACCOUNT_INDICATOR,
            "Amount" => $payment->getAmountOrdered(),
            "ExpirationMMYY" => $expirationMMYY,
            "PaymentDescriptor" => self::PAYMENT_DESCRIPTOR,
            "PaymentSource" => self::PAYMENT_SOURCE,
            "StoreCard" => self::STORE_CARD,
            "TransactionID" => $payment->getTransactionId(),
        ];
        $paymentVerificationData = $this->getPaymentVerificationData();

        return array_merge($paymentData, $paymentVerificationData);
    }

    /**
     * Get formatted value of month and year according to vesta API
     *
     * @param type $month
     * @param type $year
     *
     * @return string concatenated month and year value
     */
    private function prepareExpMonthYearMonth($month = null, $year = null)
    {
        $monthYear = '0323';
        if ($month != '' && $year != '') {
            $length = strlen((string) $month);
            $month = ($length < 2) ? "0" . $month : $month;
            $year = mb_substr($year, 2, 2);
            $monthYear = $month . $year;
        }
        return $monthYear;
    }

    /**
     * get payment avs, cvv, auth code data array
     *
     * @return array data array
     */
    private function getPaymentVerificationData()
    {
        $payment = $this->order->getPayment();
        $paymentMethod = $payment->getMethod();
        $additionalInfo = $payment->getAdditionalInformation();
        $processorAuthCode = $this->configHelper->getProcessorResponseCode($paymentMethod, self::PROCESSER_AUTH_CODE);
        $processerAvsCode = $this->configHelper->getProcessorResponseCode($paymentMethod, self::PROCESSER_AVS_CODE);
        $processerCvvCode = $this->configHelper->getProcessorResponseCode($paymentMethod, self::PROCESSER_CVV_CODE);
        $dataArray = [
            "AcquirerAuthResultCode" => isset($additionalInfo[$processorAuthCode])
                                    ? $additionalInfo[$processorAuthCode] : '',
            "AcquirerAVSResultCode" => isset($additionalInfo[$processerAvsCode])
                                    ? $additionalInfo[$processerAvsCode] : '',
            "AcquirerCVVResultCode" => isset($additionalInfo[$processerCvvCode])
                                    ? $additionalInfo[$processerCvvCode] : '',
        ];

        return $dataArray;
    }
}
