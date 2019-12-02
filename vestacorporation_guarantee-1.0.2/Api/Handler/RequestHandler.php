<?php

/**
 * Vesta Guarantee request handler.
 *
 * @author Chetu Team.
 */

namespace Vesta\Guarantee\Api\Handler;

use Vesta\Guarantee\Api\RequestApi;
use Vesta\Guarantee\Builder\ParamBuilder;
use Vesta\Guarantee\Helper\ConfigHelper;
use \Psr\Log\LoggerInterface as Logger;

/**
 * Vesta API requests related functions.
 *
 * @author Chetu Team.
 */
class RequestHandler
{

    /**
     * APi Parameters.
     *
     * @var mixed
     */
    private $requestParams;

    /**
     * Parameter builder.
     *
     * @var mixed
     */
    private $paramBuilder;

    /**
     * Log information in log file
     *
     * @var LoggerInterface
     */
    public $logger;

    /**
     * Configuration object
     *
     * @var Object
     */
    private $configHelper;

    /**
     * API call helper
     *
     * @var Object
     */
    private $apiHelper;

    /**
     * Request builder class
     *
     * @param ParamBuilder $builder
     * @param Logger       $logger
     * @param ConfigHelper $config
     * @param RequestApi   $api
     *
     * @return void
     */
    public function __construct(
        ParamBuilder $builder,
        Logger $logger,
        ConfigHelper $config,
        RequestApi $api
    ) {
        $this->paramBuilder = $builder;
        $this->logger = $logger;
        $this->configHelper = $config;
        $this->apiHelper = $api;
    }

    /**
     * Process API call
     *
     * @param Object $order
     *
     * @return mixed
     */
    public function processRequest($order = null)
    {
        $this->requestParams = $this->paramBuilder->build($order)->getApiParams();
        try {
            if (!empty($this->requestParams)) {
                $fraudApiUrl = $this->configHelper->getFraudRequestApi();

                return $this->apiHelper->makeApiCall($fraudApiUrl, $this->requestParams);
            } else {
                $this->logger->info(__('vesta parameters empty'));

                return null;
            }
        } catch (\Exception $e) {
            $this->logger->info(__('vesta error response') . $e->getMessage());

            return null;
        }
    }
}
