<?php

/**
 * PaymentAdapter File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Model\Adapter;

use Vesta\Payment\Gateway\Config\PaymentConfig;
use Vesta\Payment\Model\ClientAPI;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Encryption\EncryptorInterface;
use Vesta\Payment\Gateway\Validator\ResponseDataValidator;
use Vesta\Payment\Helper\VestaHelper;
use \Magento\Backend\Model\Session\Quote as AdminCheckout;

/**
 * PaymentAdapter Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class PaymentAdapter
{
    const PAYMENT_STATUS = [10, 52];
    /**
     *
     * @var PaymentConfig
     */
    private $config;

    /**
     *
     * @var settings
     */
    private $settings;
    private $checkoutOrderSession;
    private $helper;

    /**
     *
     * @var MerchantId
     */
    private $merchantName;

    /**
     *
     * @var RoutingId
     */
    private $routingId;

    /**
     *
     * @var PaymentDescriptar
     */
    private $paymentDesc;

    /**
     *
     * @var MerchantPin
     */
    private $merchantPassword;

    /**
     *
     * @var ccAuthOnlyResponse
     */
    private $ccAuthOnlyResponse;

    /**
     *
     * @var ccCompleteResponse
     */
    private $ccCompleteResponse;

    /**
     *
     * @var ccReturnResponse
     */
    private $ccReturnResponse;

    /**
     *
     * @var ccVoidResponse
     */
    private $ccVoidResponse;

    /**
     *
     * @var requestUrl
     */
    private $requestUrl;

    /**
     *
     * @var requestUrl
     */
    private $logs;

    /**
     * Web session
     *
     * @var mixed
     */
    public $webSession;

    /**
     * encrypter/decryptor
     *
     * @var string
     */
    private $encryptor;

    /**
     * vesta API
     *
     * @var string
     */
    private $vestaApi;

    /**
     * validator
     *
     * @var string
     */
    protected $validator;

    /**
     *
     * @param PaymentConfig $config
     */

     /**
      *
      * @var $adminCheckoutSession
      */
    private $adminCheckoutSession;

    public function __construct(
        PaymentConfig $_config,
        \Psr\Log\LoggerInterface $_logs,
        SessionManagerInterface $_session,
        EncryptorInterface $_encryptor,
        ResponseDataValidator $_validator,
        Session $_chksession,
        VestaHelper $_vestaHelper,
        ClientAPI $_vestaApi,
        AdminCheckout $_adminCheckoutSession
    ) {
        $this->config = $_config;
        $this->logs = $_logs;
        $this->webSession = $_session;
        $this->encryptor = $_encryptor;
        $this->validator = $_validator;
        $this->checkoutOrderSession = $_chksession;
        $this->helper = $_vestaHelper;
        $this->vestaApi = $_vestaApi;
        $this->adminCheckoutSession = $_adminCheckoutSession;
        $this->initCredentials();
    }

    /**
     * Initializes credentials.
     *
     * @return void
     */
    private function initCredentials()
    {
        $accountName = $this->config->getPaymentConfigData(PaymentConfig::KEY_MERCHANT_ACCOUNT_ID);
        $password = $this->config->getPaymentConfigData(PaymentConfig::KEY_MERCHANT_PASSWORD);
        $this->merchantName = $this->encryptor->decrypt($accountName);
        $this->merchantPassword = $this->encryptor->decrypt($password);
        $this->routingId = $this->config->getPaymentConfigData(PaymentConfig::KEY_ROUTING_ID);
        $this->paymentDesc = $this->config->getPaymentConfigData(PaymentConfig::KEY_PARTENAR_KEY);
    }

    /**
     * Prepare for Expiry date and month
     *
     * @param array $parameters
     * @return array
     */
    private function expMonthYearManager($parameters)
    {
        $month = (strlen($parameters['ExpirationMM']) < 2) ?
            "0".$parameters['ExpirationMM'] : $parameters['ExpirationMM'];
        $year = mb_substr($parameters['ExpirationYY'], 2, 2);
        $parameters['ExpirationMMYY'] = $month.$year;
        unset($parameters['ExpirationMM']);
        unset($parameters['ExpirationYY']);
        return $parameters;
    }

    /**
     * Submit "authorize" request
     *
     * @param  array $parameters Input parameters
     * @return array Response from ClientAPI
     * */
    public function authorize(array $parameters = [])
    {
        $parameters = $this->expMonthYearManager($parameters);
        $this->setTransactionTypeUrl('ccauthonly');
        $this->settings = [
            "AccountName" => $this->merchantName,
            "Password" => $this->merchantPassword,
            "MerchantRoutingID" => $this->routingId,
            "PaymentDescriptor" => $this->paymentDesc,
            "RequestUrl" => $this->requestUrl,
        ];
        $parameters['AutoDisposition'] = '0';

        $this->vestaApi->initVestaApi($this->settings);

        $this->ccAuthOnlyResponse = $this->vestaApi->request("ccauthonly", $parameters);
        if ($this->config->getPaymentConfigData('debug_log') == 1) {
            $this->logs->log(
                \Psr\Log\LogLevel::INFO,
                'Debug Authorize Only Process Response from Vesta Payment'
            );
            $this->logs->log(
                \Psr\Log\LogLevel::INFO,
                json_encode($this->ccAuthOnlyResponse, JSON_PRETTY_PRINT)
            );
            $this->saveResponse($this->ccAuthOnlyResponse, "authorise");
        }

        return $this->ccAuthOnlyResponse;
    }

    /**
     * Submit "cccomplete" request
     *
     * @param  array $parameters Input parameters
     * @return array Response from ClientAPI
     * */
    public function cccomplete($txn_Id = null, $amount = null)
    {
        $this->setTransactionTypeUrl('cccomplete');
        $this->settings = [
            "AccountName" => $this->merchantName,
            "Password" => $this->merchantPassword,
            "RequestUrl" => $this->requestUrl,
        ];
        $this->vestaApi->initVestaApi($this->settings);
        $parameters = [
            'PaymentID' => $txn_Id,
            'Amount' => $amount,
            "DispositionType" => "1",
            "TransactionID" => $this->webSession->getTransId(),
            "DispositionComment" => "Vesta Capture Method",
        ];
        $this->ccCompleteResponse = $this->vestaApi->request("cccomplete", $parameters);
        if ($this->config->getPaymentConfigData('debug_log') == 1) {
            $this->logs->log(
                \Psr\Log\LogLevel::INFO,
                'Debug CC Complete (Capture) Process Response from Vesta Payment'
            );
            $this->logs->log(
                \Psr\Log\LogLevel::INFO,
                json_encode($this->ccCompleteResponse, JSON_PRETTY_PRINT)
            );
            $this->saveResponse($this->ccCompleteResponse, "capture");
        }

        return $this->ccCompleteResponse;
    }

    /**
     * Submit "ccsale" request
     *
     * @param  array $parameters Input parameters
     * @return array Response from Converge
     * */
    public function sale(array $parameters = [])
    {
        $parameters = $this->expMonthYearManager($parameters);
        $parameters['AutoDisposition'] = '1';
        $this->setTransactionTypeUrl('ccsale');
        $this->settings = [
            "AccountName" => $this->merchantName,
            "Password" => $this->merchantPassword,
            "MerchantRoutingID" => $this->routingId,
            "PaymentDescriptor" => $this->paymentDesc,
            "RequestUrl" => $this->requestUrl,
        ];
        $this->vestaApi->initVestaApi($this->settings);

        $this->ccSaleResponse = $this->vestaApi->request("ccsale", $parameters);
        if ($this->config->getPaymentConfigData('debug_log') == 1) {
            $this->logs->log(
                \Psr\Log\LogLevel::INFO,
                'Debug Authorize & Capture Only Process Response from Vesta Payment'
            );
            $this->logs->log(
                \Psr\Log\LogLevel::INFO,
                json_encode($this->ccSaleResponse, JSON_PRETTY_PRINT)
            );
            $this->saveResponse($this->ccSaleResponse, "auth_capture");
        }

        return $this->ccSaleResponse;
    }

    /**
     * Submit "ccreturn" request
     *
     * @param  array $parameters Input parameters
     * @return array Response from ClientAPI
     * */
    public function ccreturn($txn_Id, $amount = null)
    {
        $this->setTransactionTypeUrl('ccreturns');
        $this->settings = [
            "AccountName" => $this->merchantName,
            "Password" => $this->merchantPassword,
            "RequestUrl" => $this->requestUrl,
        ];
        $this->vestaApi->initVestaApi($this->settings);

        $parameters = [
            'PaymentID' => $txn_Id,
            'Amount' => $amount,
            "TransactionID" => $this->webSession->getTransId(),
        ];

        $this->ccReturnResponse = $this->vestaApi->request("ccreturn", $parameters);

        if ($this->config->getPaymentConfigData('debug_log') == 1) {
            $this->logs->log(
                \Psr\Log\LogLevel::INFO,
                'Debug Refund Process Response from Vesta Payment'
            );
            $this->logs->log(
                \Psr\Log\LogLevel::INFO,
                json_encode($this->ccReturnResponse, JSON_PRETTY_PRINT)
            );
            $this->saveResponse($this->ccReturnResponse, "refund");
        }

        return $this->ccReturnResponse;
    }

    /**
     * Submit "void" request
     *
     * @param  array $parameters Input parameters
     * @return array Response from ClientAPI
     * */
    public function void($txn_Id = null, $amount = null)
    {
        $this->setTransactionTypeUrl('ccvoid');
        $this->settings = [
            "AccountName" => $this->merchantName,
            "Password" => $this->merchantPassword,
            "RequestUrl" => $this->requestUrl,
        ];
        $this->vestaApi->initVestaApi($this->settings);
        
        $parameters = [
            'PaymentID' => $txn_Id,
            'Amount' => $amount,
            "TransactionID" => $this->webSession->getTransId(),
        ];
        $this->ccVoidResponse = $this->vestaApi->request("ccvoid", $parameters);

        if ($this->config->getPaymentConfigData('debug_log') == 1) {
            $this->logs->log(
                \Psr\Log\LogLevel::INFO,
                'Debug Void Process Response from Vesta Payment'
            );
            $this->logs->log(
                \Psr\Log\LogLevel::INFO,
                json_encode($this->ccVoidResponse, JSON_PRETTY_PRINT)
            );
            $this->saveResponse($this->ccVoidResponse, "void");
        }

        return $this->ccVoidResponse;
    }

    /**
     * @param $parameters
     * @return void
     */
    private function setTransactionTypeUrl($type = null)
    {
        if ($type == 'ccauthonly') {
            $apiLink = $this->config->getPaymentConfigData(PaymentConfig::KEY_SDK_URL);
            $apiName = $this->config->getPaymentConfigData(PaymentConfig::KEY_GET_CHARGE_AUTH_URL);
            $apiEndPoint = rtrim($apiLink, "/") . '/' . $apiName;
            $this->requestUrl = $apiEndPoint;
        }
        if ($type == 'ccsale') {
            $apiLink = $this->config->getPaymentConfigData(PaymentConfig::KEY_SDK_URL);
            $apiName = $this->config->getPaymentConfigData(PaymentConfig::KEY_GET_CHARGE_AUTH_URL);
            $apiEndPoint = rtrim($apiLink, "/") . '/' . $apiName;
            $this->requestUrl = $apiEndPoint;
        }
        if ($type == 'cccomplete') {
            $apiLink = $this->config->getPaymentConfigData(PaymentConfig::KEY_SDK_URL);
            $apiName = $this->config->getPaymentConfigData(PaymentConfig::KEY_GET_CHARGE_AUTH_CONFIRM_URL);
            $apiEndPoint = rtrim($apiLink, "/") . '/' . $apiName;
            $this->requestUrl = $apiEndPoint;
        }
        if ($type == 'ccreturns') {
            $apiLink = $this->config->getPaymentConfigData(PaymentConfig::KEY_SDK_URL);
            $apiName = $this->config->getPaymentConfigData(PaymentConfig::KEY_GET_REFUND_URL);
            $apiEndPoint = rtrim($apiLink, "/") . '/' . $apiName;
            $this->requestUrl = $apiEndPoint;
        }
        if ($type == 'ccvoid') {
            $apiLink = $this->config->getPaymentConfigData(PaymentConfig::KEY_SDK_URL);
            $apiName = $this->config->getPaymentConfigData(PaymentConfig::KEY_GET_VOID_URL);
            $apiEndPoint = rtrim($apiLink, "/") . '/' . $apiName;
            $this->requestUrl = $apiEndPoint;
        }
    }

    /**
     * Use to save response
     *
     * @param array $response
     * @param string $code
     * @param string $message
     * @return void
     */
    private function saveResponse($response = [], $type = null)
    {

        if (isset($response['ResponseCode']) && $response['ResponseCode'] == "0" &&
        in_array($response['PaymentStatus'], self::PAYMENT_STATUS)
        ) {
            $reservedId = $this->checkoutOrderSession->getQuote()->getReservedOrderId();
            if ($reservedId == null) {
                $reservedId = $this->helper->getCurrentOrderId();
            }
            if ($reservedId == null) {
                $reservedId = $this->adminCheckoutSession->getQuote()->getReservedOrderId();
            }
            $data['log_content'] = $this->getTxnTypeResponse($type);
            $data['order_id'] = $reservedId;
            $data['response_code'] = $response['ResponseCode'];
            $this->helper->insertPaymentlog($data);
        }
        return true;
    }

    /**
     * Use to get transaction type response
     *
     * @param array $type
     * @return string
     */
    private function getTxnTypeResponse($type = null)
    {
        if ($type == "auth_capture") {
            return __("Payment_Auth_Capture_Call_Successful");
        }
        if ($type == "capture") {
            return __("Payment_Capture_Call_Successful");
        }
        if ($type == "authorise") {
            return __("Payment_AUTH_Call_Successful");
        }
        if ($type == "refund") {
            return __("Payment_Refund_Call_Successful");
        }
        if ($type == "void") {
            return __("Payment_Void_Call_Successful");
        }
    }
}
