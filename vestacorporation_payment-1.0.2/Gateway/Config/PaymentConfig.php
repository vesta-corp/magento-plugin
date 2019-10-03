<?php

/**
 * PaymentConfig File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface as ConfigurationScope;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * PaymentConfig Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class PaymentConfig extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ACTIVE = 'active';
    const KEY_MERCHANT_ACCOUNT_ID = 'api_username';
    const KEY_MERCHANT_PASSWORD = 'api_password';
    const KEY_CC_TYPES = 'cctypes';
    const KEY_PAYMENT_ACTION = 'payment_action';
    const KEY_SDK_URL = 'api_url';
    const KEY_TOKEN_SDK_URL = 'vesta_token_url';
    const KEY_GET_SESSION_URL = 'session_tag_api';
    const KEY_GET_CHARGE_SALE_URL = 'charge_sale_api';
    const KEY_GET_CHARGE_AUTH_URL = 'authorize_sale_api';
    const KEY_GET_CHARGE_AUTH_CONFIRM_URL = 'authorize_confirm_api';
    const KEY_GET_REFUND_URL = 'reverse_payment_api';
    const KEY_GET_VOID_URL = 'reverse_payment_api';
    const KEY_GET_TEMP_TOKEN_URL = 'vesta_temp_token_api';
    const KEY_PAYMENT_DESCRIPTAR = 'payment_descriptor';
    const KEY_PARTENAR_KEY = 'api_customerkey';
    const KEY_ROUTING_ID = 'api_merchantroutingiD';
    const KEY_SAVE_CARD = 'save_card';
    const DEBUG_LOG = 'debug_log';

    /**
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;
    
    /**
     * encrypter.
     *
     * @var string
     */
    protected $encryptor;

    /**
     * Environment Type.
     *
     * @var string
     */
    private $environmentType;

    /**
     * Vesta config constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param string               $pathPattern
     * @param Json|null            $serializer
     */
    public function __construct(
        ScopeConfigInterface $_scopeConfig,
        EncryptorInterface $_encryptor,
        $pathPattern = self::DEFAULT_PATH_PATTERN,
        Json $serializer = null
    ) {
        $this->scopeConfig = $_scopeConfig;
        $this->encryptor = $_encryptor;
        // Check Environment Type
        $this->environmentType = $this->scopeConfig->getValue(
                'payment/vesta_payment/environment_type', 
                ConfigurationScope::SCOPE_STORE
        );
        parent::__construct(
            $this->scopeConfig,
            $pathPattern
        );
        $this->serializer = $serializer ? : \Magento\Framework\App\ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Retrieve information from payment configuration table
     *
     * @param string $field
     *
     * @return string
     */
    public function getPaymentConfigData($field)
    {
        $path = 'payment/vesta_payment/' . $field;
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Return the country specific card type config
     *
     * @return array
     */
    public function getCountrySpecificCardTypeConfig()
    {
        $countryCardTypes = $this->getValue(self::KEY_COUNTRY_CREDIT_CARD);
        if (!$countryCardTypes) {
            return [];
        }
        $countryCardTypes = $this->serializer->unserialize($countryCardTypes);
        return is_array($countryCardTypes) ? $countryCardTypes : [];
    }

    /**
     * Retrieve available credit card types
     *
     * @return array
     */
    public function getAvailableCardTypes()
    {
        $ccTypes = $this->getValue(self::KEY_CC_TYPES);
        return !empty($ccTypes) ? explode(',', $ccTypes) : [];
    }

    /**
     *
     * @return string
     */
    public function getMerchantPassword()
    {
        $password = $this->getValue($this->environmentType.'_'.Config::KEY_MERCHANT_PASSWORD);
        return $this->encryptor->decrypt($password);
    }

    /**
     *
     * @return string
     */
    public function getSdkUrl()
    {
        return $this->getValue($this->environmentType.'_'.Config::KEY_SDK_URL);
    }

    /**
     *
     * @return string
     */
    public function getSessionTagUrl()
    {
        return $this->getValue(Config::KEY_GET_SESSION_URL);
    }

    /**
     *
     * @return string
     */
    public function getChargeSaleUrl()
    {
        return $this->getValue(Config::KEY_GET_CHARGE_SALE_URL);
    }

    /**
     *
     * @return string
     */
    public function getChargeAuthUrl()
    {
        return $this->getValue(Config::KEY_GET_CHARGE_AUTH_URL);
    }

    /**
     *
     * @return string
     */
    public function getAuthConfirmUrl()
    {
        return $this->getValue(Config::KEY_GET_CHARGE_AUTH_CONFIRM_URL);
    }

    /**
     *
     * @return string
     */
    public function getRefundUrl()
    {
        return $this->getValue(Config::KEY_GET_REFUND_URL);
    }

    /**
     * Get Payment configuration status
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getValue(self::KEY_ACTIVE);
    }

    /**
     * Get Merchant account ID
     *
     * @return string
     */
    public function getMerchantAccountId()
    {
        $accountName = $this->getPaymentConfigData($this->environmentType.'_'.self::KEY_MERCHANT_ACCOUNT_ID);
        return $this->encryptor->decrypt($accountName);
    }

    /**
     * Get CardSaveStatus
     *
     * @return string
     */
    public function getCardSaveStatus()
    {
        return $this->getPaymentConfigData(self::KEY_SAVE_CARD);
    }
    /**
     * Get getMercahtRouting
     *
     * @return string
     */
    public function getMercahtRouting()
    {
        return $this->getPaymentConfigData($this->environmentType.'_'.self::KEY_ROUTING_ID);
    }

    /**
     * Get getPaymentDescripter
     *
     * @return string
     */
    public function getPaymentDescripter()
    {
        return $this->getPaymentConfigData(self::KEY_PAYMENT_DESCRIPTAR);
    }

    /**
     * Get Temporary token api
     *
     * @return string
     */
    public function getVestaTokenAPI()
    {
        $sdkUrl = $this->getPaymentConfigData($this->environmentType.'_'.self::KEY_TOKEN_SDK_URL);
        $tempTokenUrl = $this->getPaymentConfigData(self::KEY_GET_TEMP_TOKEN_URL);
        $apiEndPoint = rtrim($sdkUrl, "/") . '/' . $tempTokenUrl;

        return $apiEndPoint;
    }
}
