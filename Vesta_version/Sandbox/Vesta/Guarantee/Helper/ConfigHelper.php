<?php
/**
 * Vesta Guarantee helper functions.
 *
 * @author Chetu Team.
 */

namespace Vesta\Guarantee\Helper;

use Magento\Framework\Session\SessionManagerInterface;
use Vesta\Guarantee\Api\RequestApi;
use Vesta\Guarantee\Model\Logs;
use \Magento\Customer\Model\Session;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\ScopeInterface as ConfigurationScope;
use \Magento\Vault\Api\PaymentTokenManagementInterface;
use \Psr\Log\LoggerInterface as Logger;
use \Magento\Framework\Encryption\EncryptorInterface;

/**
 * Configuration details in Vesta Guarantee module.
 *
 * @author Chetu Team.
 */
class ConfigHelper
{
    /**
     * Session object
     *
     * @var mixed
     */
    protected $session;

    /**
     * Configuration Flag.
     *
     * @var bool
     */
    private $configFlag = true;

    /**
     * Username.
     *
     * @var string
     */
    public $userName;

    /**
     * Password.
     *
     * @var string
     */
    private $password;

    /**
     * End point URL.
     *
     * @var string
     */
    public $endPointUrl;

    /**
     * Merchant routing ID.
     *
     * @var string
     */
    private $merchantRoutingID;

    /**
     * Auto disposition value.
     *
     * @var bool
     */
    private $autoDisposition;

    /**
     * Partner Customer Key value.
     *
     * @var bool
     */
    private $partnerCustomerKey;

    /**
     * Web session
     *
     * @var mixed
     */
    public $webSession;

    /**
     * Scope configuration.
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * API call helper
     *
     * @var Object
     */
    private $apiHelper;

    /**
     * Log information in log file
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * vesta API response logger
     *
     * @var mixed
     */
    private $resLogger;

    /**
     * payment token.
     *
     * @var mixed
     */
    protected $paymentToken;

    /**
     * customer session.
     *
     * @var mixed
     */
    protected $customerSession;

    /**
     * Acquirer CD.
     *
     * @var string
     */
    private $acquirerCD;
        
    /**
     * encrypter.
     *
     * @var string
     */
    protected $encryptor;

    public function __construct(
        ScopeConfigInterface $_scopeInf,
        Logger $_logger,
        RequestApi $_api,
        Logs $_logs,
        SessionManagerInterface $_session,
        PaymentTokenManagementInterface $_paymentToken,
        Session $_customerSession,
        EncryptorInterface $_encryptor
    ) {
        $this->scopeConfig = $_scopeInf;
        $this->logger = $_logger;
        $this->setConfiguration();
        $this->apiHelper = $_api;
        $this->resLogger = $_logs;
        $this->webSession = $_session;
        $this->paymentToken = $_paymentToken;
        $this->customerSession = $_customerSession;
        $this->encryptor = $_encryptor;
    }

    /**
     * Get configuration of module
     *
     * @return void
     */
    private function setConfiguration()
    {
        $this->userName = $this->scopeConfig->getValue(
            'vesta_protection/general/account_name',
            ConfigurationScope::SCOPE_STORE
        );
        $this->password = $this->scopeConfig->getValue(
            'vesta_protection/general/password',
            ConfigurationScope::SCOPE_STORE
        );
        $this->endPointUrl = $this->scopeConfig->getValue(
            'vesta_protection/general/end_point_url',
            ConfigurationScope::SCOPE_STORE
        );
        $this->merchantRoutingID = $this->scopeConfig->getValue(
            'vesta_protection/general/merchant_routing_id',
            ConfigurationScope::SCOPE_STORE
        );
        $this->partnerCustomerKey = $this->scopeConfig->getValue(
            'vesta_protection/general/partner_customer_key',
            ConfigurationScope::SCOPE_STORE
        );
        $this->autoDisposition = $this->scopeConfig->getValue(
            'vesta_protection/general/autodisposition',
            ConfigurationScope::SCOPE_STORE
        );
        $this->acquirerCD = $this->scopeConfig->getValue(
            'vesta_protection/general/acquirer_cd',
            ConfigurationScope::SCOPE_STORE
        );
    }

    /**
     * Check configuration setting.
     *
     * @return bool
     */
    private function checkConfiguration()
    {
        if ($this->userName == '') {
            $this->logger->info(__("vesta guarantee config error - username"));
            $this->configFlag = false;
        }
        if ($this->password == '') {
            $this->logger->info(__("vesta guarantee config error - password"));
            $this->configFlag = false;
        }
        if ($this->endPointUrl == '') {
            $this->logger->info(__("vesta guarantee config error - endpoint"));
            $this->configFlag = false;
        }
        if ($this->merchantRoutingID == '') {
            $this->logger->info(__("vesta guarantee config error - merchantrouting"));
            $this->configFlag = false;
        }
        if ($this->partnerCustomerKey == '') {
            $this->logger->info(__("vesta guarantee config error - partnercustomerkey"));
            $this->configFlag = false;
        }
        if ($this->acquirerCD == '') {
            $this->logger->info(__("vesta guarantee config error - acquirerCD"));
            $this->configFlag = false;
        }

        return $this->configFlag;
    }

    /**
     * Check module is enabled or not.
     *
     * @return bool
     */
    private function isEnabled()
    {
        if ($this->scopeConfig->getValue('vesta_protection/general/enable', ConfigurationScope::SCOPE_STORE)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check module is activated and all configuration is active.
     *
     * @return bool
     */
    public function isActive()
    {

        if ($this->isEnabled() && $this->checkConfiguration()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if merchant is autherised for current transaction.
     *
     * @param string $transactionId Current order transaction Id
     *
     * @return mixed
     */
    public function authorise($transactionId = null, $order = null)
    {
        if (!empty($this->webSession->getWebSessionID())) {
            return true;
        } else {
            $response = $this->getSessionData($transactionId);
            if (!empty($response) && is_array($response) && isset($response['ResponseCode'])
                 && $response['ResponseCode'] == 0 && isset($response['WebSessionID']) &&
                !empty($response['WebSessionID'])
            ) {
                $this->webSession->start();
                $this->webSession->setWebSessionID($response['WebSessionID']);
                $this->webSession->setOrgID($response['OrgID']);

                return true;
            } else {
                $this->saveAuthResponse($response, $order);
                return false;
            }
        }
    }

    /**
     * Save authorization response
     *
     * @param array $response
     * @param array $order
     *
     * @return void
     */
    private function saveAuthResponse($response = null, $order = null)
    {
        $err_res = (is_array($response) && isset($response['ResponseText'])) ?
            $response['ResponseText'] : __('vesta guarantee empty response');

        if ($order != null) {
            $this->resLogger->setResponseData(__("vesta auth error") . $err_res)
                ->setOrderId($order->getIncrementId())
                ->save();
        }
    }

    /**
     * Get session Tags
     *
     * @param string $transactionId current transaction id or any random string
     *
     * @return Json response data
     */
    private function getSessionData($transactionId = null)
    {
        try {
            $data = [
                "AccountName" => $this->encryptor->decrypt($this->userName),
                "Password" => $this->encryptor->decrypt($this->password),
                "TransactionID" => $transactionId,
            ];
            $sessionTagApi = $this->getSessionTagApi();
            $response = $this->apiHelper->makeApiCall($sessionTagApi, $data);

            return $response;
        } catch (\Exception $ex) {
            $this->logger->info(__("vesta error response") . $ex->getMessage());

            return null;
        }
    }

    /**
     * Get module configuration parameters
     *
     * @return Array array of configuration
     */
    public function getConfigParams()
    {
        $data = [
            "AccountName" => $this->encryptor->decrypt($this->userName),
            "MerchantRoutingID" => $this->merchantRoutingID,
            "PartnerCustomerKey" => $this->partnerCustomerKey,
            "Password" => $this->encryptor->decrypt($this->password),
            "AutoDisposition" => $this->autoDisposition,
            "AcquirerCD" => $this->acquirerCD,
            "WebSessionID" => $this->webSession->getWebSessionID(),
        ];

        return $data;
    }

    /**
     * get storage vault
     *
     * @param  $order object
     * @return bool
     */
    public function isPdof($order = null)
    {
        if ($this->customerSession->isLoggedIn()) {
            $customerId = $order->getCustomerId();
            $payment = $order->getPayment();
            $cclast4 = $payment->getCcLast4();
            $month = $payment->getCcExpMonth();
            $year = $payment->getCcExpYear();
            $expirationDate = $month . "/" . $year;
            $cardList = $this->paymentToken->getListByCustomerId($customerId);
            $flag = "false";
            if (!empty($cardList)) {
                foreach ($cardList as $card) {
                    $data = json_decode($card->getDetails(), true);
                    if ($data['maskedCC'] == $cclast4 && $data['expirationDate'] == $expirationDate
                        && $card->getPaymentMethodCode() == $payment->getMethod()
                    ) {
                        $flag = "true";
                        break;
                    }
                }
                return $flag;
            } else {
                return "false";
            }
        } else {
            return "false";
        }
    }
    
    /**
     * Get Card stored date.
     *
     * @return string
     */
    public function getCardStoredDTM($order = null)
    {
        if ($this->customerSession->isLoggedIn()) {
            $customerId = $order->getCustomerId();
            $payment = $order->getPayment();
            $cclast4 = $payment->getCcLast4();
            $month = $payment->getCcExpMonth();
            $year = $payment->getCcExpYear();
            $expirationDate = $month . "/" . $year;
            $cardList = $this->paymentToken->getListByCustomerId($customerId);
            $CardStoredDTM = "";
            if (!empty($cardList)) {
                foreach ($cardList as $card) {
                    $data = json_decode($card->getDetails(), true);
                    if ($data['maskedCC'] == $cclast4 && $data['expirationDate'] == $expirationDate &&
                        $card->getPaymentMethodCode() == $payment->getMethod()
                    ) {
                        $CardStoredDTM = $card->getCreatedAt();
                    }
                }
                return $CardStoredDTM;
            } else {
                return "";
            }
        } else {
            return "";
        }
    }

    /**
     * Check guarantee is enabled.
     *
     * @return bool
     */
    public function isActiveForVestaPay()
    {
        if ($this->scopeConfig->getValue(
            'payment/vesta_payment/vesta_fraudprotection_active',
            ConfigurationScope::SCOPE_STORE
        )
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get vesta session tag API URL
     *
     * @return string
     */
    public function getSessionTagApi()
    {
        $sessionTagApi = $this->scopeConfig->getValue(
            'vesta_protection/general/vesta_session_tag_api',
            ConfigurationScope::SCOPE_STORE
        );
        $apiEndPoint = rtrim($this->endPointUrl, "/") . '/';

        return $apiEndPoint.$sessionTagApi;
    }

    /**
     * get vesta risk API URL
     *
     * @return string
     */
    public function getFraudRequestApi()
    {
        $fraudRequestApi = $this->scopeConfig->getValue(
            'vesta_protection/general/vesta_fraud_api',
            ConfigurationScope::SCOPE_STORE
        );
        $apiEndPoint = rtrim($this->endPointUrl, "/") . '/';

        return $apiEndPoint.$fraudRequestApi;
    }

    /**
     * get method response codes
     *
     * @return string
     */
    public function getProcessorResponseCode($method = null, $code = null)
    {
        $code = $this->scopeConfig->getValue(
            "payment_processer_codes/{$method}/{$code}",
            ConfigurationScope::SCOPE_STORE
        );

        return $code;
    }
}
