<?php
/**
 * Vesta Guarantee related services.
 *
 * @author Chetu Team.
 */

namespace Vesta\Guarantee\Services;

use Vesta\Guarantee\Api\Handler\RequestHandler;
use Vesta\Guarantee\Api\Handler\ResponseHandler;
use Vesta\Guarantee\Helper\ConfigHelper;
use Vesta\Guarantee\Model\Logs;
use \Psr\Log\LoggerInterface as Logger;

/**
 * Vesta Guarantee API requests and responses.
 *
 * @author Chetu Team.
 */
class FraudCaseService
{

    /**
     * Configuration object
     *
     * @var Object
     */
    private $configHelper;

    /**
     * API response handler.
     *
     * @var mixed
     */
    private $responseHandler;

    /**
     * Log information in log file
     *
     * @var LoggerInterface
     */
    public $logger;

    /**
     * Response logger
     *
     * @var mixed
     */
    public $resLogger;

    /**
     * API request.
     *
     * @var RequestHandler
     */
    private $requestHandler;

    /**
     * Constructor.
     *
     * @param ConfigHelper    $config
     * @param Logger          $logger
     * @param RequestHandler  $requestHandler
     * @param ResponseHandler $response
     *
     * @return bool
     */
    public function __construct(
        ConfigHelper $_config,
        Logger $_logger,
        RequestHandler $_requestHandler,
        ResponseHandler $_response,
        Logs $_logs
    ) {
        $this->configHelper = $_config;
        $this->logger = $_logger;
        $this->requestHandler = $_requestHandler;
        $this->responseHandler = $_response;
        $this->resLogger = $_logs;
    }

    /**
     * Create case for recent order
     *
     * @param Object $observer Observer class object
     *
     * @return boolean
     */
    public function createCase($observer = null)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order == null || $order->getPayment() == null) {
            $this->logger->info('Order data is not available');
            return false;
        }
        $transactionId = $order->getPayment()->getTransactionId();
        if (!$this->configHelper->isActive()) {
            return false;
        }
        if ($order->getPayment()->getMethod() == 'vesta_payment' && !$this->configHelper->isActiveForVestaPay()) {
            $this->logger->info('Guarantee is disabled for Vesta Payment');
            return false;
        }
        if (!$this->isApplicable($order)) {
            $this->resLogger->setResponseData(__("Not applicable") . "{$order->getPayment()->getMethod()}")
                ->setOrderId($order->getIncrementId())
                ->save();
            return false;
        }
        
        if (!$this->configHelper->authorise($transactionId, $order)) {
            return false;
        }
        
        $response = $this->requestHandler->processRequest($order);
        $this->responseHandler->handle($response, $order);

        return true;
    }

    /**
     * Check if payment completed by card
     *
     * @param Order $order class object.
     *
     * @return bool returns true/false based on applicable payment type
     */
    private function isApplicable($order = null)
    {
        if (empty($order->getPayment()->getCcLast4())) {
            return false;
        } else {
            return true;
        }
    }
}
