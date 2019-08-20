<?php

/**
 * ClientAPI File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Model;

use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\HTTP\ZendClientFactory;
use \Psr\Log\LoggerInterface;

/**
 * ClientAPI Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

class ClientAPI
{
    /**
     * Vesta API setting
     *
     * @var array
     */
    private $settings = [];

    /**
     * http
     *
     * @var mixed
     */
    private $httpClientFactory;

    /**
     * logger
     *
     * @var mixed
     */
    private $logger;

    /**
     * Transaction types
     *
     * @var array
     */
    private $transactionTypes = [
        'ccauthonly',
        'ccavsonly',
        'ccsale',
        'ccverify',
        'ccgettoken',
        'cccredit',
        'ccforce',
        'ccbalinquiry',
        'ccgettoken',
        'ccreturn',
        'ccvoid',
        'cccomplete',
        'ccdelete',
        'ccupdatetip',
        'ccsignature',
        'ccaddrecurring',
        'ccaddinstall'
    ];

    /**
     * Constructor
     *
     * @param ZendClientFactory $_httpClientFactory
     */
    public function __construct(ZendClientFactory $_httpClientFactory, LoggerInterface $_logs)
    {
        $this->httpClientFactory = $_httpClientFactory;
        $this->logger = $_logs;
    }

    /**
     * Construct VestaApi object with provided settings.
     * @param array $settings
     */
    public function initVestaApi(array $settings)
    {

        $this->validateSettings($settings);
        $this->settings = $settings;
    }

    /**
     * Make request to VestaAPI with transaction type and parameters.
     * @param $transactionType
     * @param array $parameters
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function request($transactionType, array $parameters)
    {
        if (!in_array($transactionType, $this->transactionTypes)) {
            throw new LocalizedException(__('Invalid transaction type specified.'));
        }
        return $this->httpRequest($parameters);
    }

    /**
     * Validate provided settings when constructing class.
     * @param array $settings
     * @return bool
     * @throws LocalizedException
     */
    private function validateSettings(array $settings)
    {
        if (!isset($settings['AccountName'])) {
            throw new LocalizedException(__('Please provide a valid merchant id in settings.'));
        }
        if (!isset($settings['Password'])) {
            throw new LocalizedException(__('Please provide a valid password in settings.'));
        }
        return true;
    }
    
    /**
     * @param $parameters
     * @return array
     */
    private function httpRequest(array $parameters = null)
    {
        $params = array_merge($this->settings, $parameters);
        $requestUrl = $params['RequestUrl'];
        //unset url parameters from main parameters
        unset($params['RequestUrl']);
        $client = $this->httpClientFactory->create();
        $client->setUri($requestUrl);
        $client->setHeaders(['Content-Type: application/json']);
        $client->setMethod(\Zend_Http_Client::POST);
        $client->setRawData(json_encode($params));
        try {
            $response = $client->request()->getBody();
            $responseData = json_decode($response, true);

            return $responseData;
        } catch (\Zend_Http_Client_Exception $e) {
            $this->logger->info($e->getMessage());
            throw new LocalizedException(__('Something went wrong please contact your service provider.'));
        }
    }
}
