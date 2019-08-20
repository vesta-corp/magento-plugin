<?php

/**
 * Payment Module get session tags API call.
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Observer;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use \Magento\Framework\HTTP\ZendClientFactory;

/**
 * Get unique session Class Doc.
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

class DataCollectorSession implements ObserverInterface
{
    public static $guarantee = "Vesta_Guarantee";
    
    /**
     * Scope configuration.
     *
     * @var ScopeConfigInterface
     */
    private $config;
    
    /**
     * Log information in log file
     *
     * @var LoggerInterface
     */
    private $logs;

    /**
     * Web session
     *
     * @var mixed
     */
    public $webSession;
    
    /**
     * encrypter.
     *
     * @var string
     */
    private $encryptor;

    /**
     * $httpClientFactory
     *
     * @var object
     */
    private $httpClientFactory;

    public function __construct(
        ScopeConfigInterface $_config,
        SessionManagerInterface $_session,
        LoggerInterface $_logs,
        EncryptorInterface $_encryptor,
        ZendClientFactory $_httpClientFactory
    ) {
        $this->config = $_config;
        $this->webSession = $_session;
        $this->logs = $_logs;
        $this->encryptor = $_encryptor;
        $this->httpClientFactory = $_httpClientFactory;
    }
    /**
     * Customer register event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return boolean
     */
    public function execute(Observer $observer)
    {
        // start session to work with session variables.
        $this->webSession->start();
        $this->authorize();
        return true;
    }

    /**
     * Check if merchant is authorized for current transaction.
     * @return mixed
     */
    public function authorize()
    {
        if (!empty($this->webSession->getWebSessionID()) &&
            !empty($this->webSession->getTransId())
        ) {
            $transactionId = $this->getToken(10);
            $this->webSession->setTransId($transactionId);
            return true;
        } else {
            $transactionId = $this->getToken(10);
            $response = $this->getSessionData($transactionId);

            if (!empty($response) && is_array($response) &&
                isset($response['ResponseCode']) && $response['ResponseCode'] == 0
            ) {
                $this->webSession->setWebSessionID($response['WebSessionID']);
                $this->webSession->setOrgID($response['OrgID']);
                $this->webSession->setTransId($transactionId);
                return true;
            } else {
                return false;
            }
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
                "AccountName" => $this->encryptor->decrypt(
                    $this->config->getValue(
                        'payment/vesta_payment/api_username',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                ),
                "Password" => $this->encryptor->decrypt(
                    $this->config->getValue(
                        'payment/vesta_payment/api_password',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                ),
                "TransactionID" => $transactionId,
            ];
            $api_url = $this->config->getValue(
                'payment/vesta_payment/api_url',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $session_tag_url = $this->config->getValue(
                'payment/vesta_payment/session_tag_api',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $apiEndPoint = rtrim($api_url, "/") . '/' . $session_tag_url;
            $response = $this->callApi($apiEndPoint, $data);

            return $response;
        } catch (\Exception $ex) {
            $this->logs->info(__("vesta error response") . $ex->getMessage());
            return null;
        }
    }

    /**
     * Use to get random string
     *
     * @param $length
     * @return $token
     */
    private function getToken($length = null)
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet .= "0123456789";
        $max = strlen($codeAlphabet);

        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[random_int(0, $max - 1)];
        }
        return $token;
    }

    /**
     * Request data
     *
     * @param $url
     * @param $parameters
     * @return array
     */
    private function callApi($url = null, $parameters = null)
    {
            $client = $this->httpClientFactory->create();
            $client->setUri($url);
            $client->setHeaders(['Content-Type: application/json']);
            $client->setMethod(\Zend_Http_Client::POST);
            $client->setRawData(json_encode($parameters));
        try {
            $response = $client->request()->getBody();

            return json_decode($response, true);
        } catch (\Exception $e) {
            $this->logs->info($e->getMessage());
        }
    }
}
