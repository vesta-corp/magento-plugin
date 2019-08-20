<?php

/**
 * Vesta Guarantee API requests.
 *
 * @author Chetu Team.
 */

namespace Vesta\Guarantee\Api;

use \Psr\Log\LoggerInterface as Logger;
use \Magento\Framework\HTTP\ZendClientFactory;
use \Magento\Framework\Exception\LocalizedException;

/**
 * Vesta Guarantee API requests related functions.
 *
 * @author Chetu Team.
 */
class RequestApi
{

    /**
     * Log information in log file
     *
     * @var LoggerInterface
     */
    public $logger;
    
    /**
     * http
     *
     * @var mixed
     */
    private $httpClientFactory;

    public function __construct(Logger $logger, ZendClientFactory $_httpClientFactory)
    {
        $this->logger = $logger;
        $this->httpClientFactory = $_httpClientFactory;
    }
    
    /**
     * send request to API server
     *
     * @param string $apiUrl  API Endpoint
     * @param mixed  $apiData API Parameters
     *
     * @return json
     */
    public function makeApiCall($apiUrl = null, $apiData = null)
    {
        
        $client = $this->httpClientFactory->create();
        $client->setUri($apiUrl);
        $client->setHeaders(['Content-Type: application/json']);
        $client->setMethod(\Zend_Http_Client::POST);
        $client->setRawData(json_encode($apiData));
        try {
            $response = $client->request()->getBody();
            $responseData = json_decode($response, true);
            $this->logger->info(print_r($responseData, true));
            return $responseData;
        } catch (\Zend_Http_Client_Exception $e) {
            $this->logger->info($e->getMessage());
            throw new LocalizedException(__('Something went wrong please contact your service provider.'));
        }
    }
}
