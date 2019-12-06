<?php
/**
 * Vesta Guarantee request parameter builder.
 *
 * @author Chetu Team.
 */

namespace Vesta\Guarantee\Builder;

use Vesta\Guarantee\Builder\Request\PaymentBuilder;
use Vesta\Guarantee\Builder\Request\PurchaseBuilder;
use Vesta\Guarantee\Builder\Request\RiskBuilder;
use Vesta\Guarantee\Helper\ConfigHelper;
use Vesta\Guarantee\Validator\XmlValidator;
use \Psr\Log\LoggerInterface as Logger;

/**
 * Vesta Guarantee API request parameters builder class.
 *
 * @author Chetu Team.
 */
class ParamBuilder
{

    /**
     * Mixed API Parameters.
     *
     * @var mixed
     */
    private $apiParams;

    /**
     * Payment data class object
     *
     * @var Object
     */
    private $paymentObj;

    /**
     * Risk data class object
     *
     * @var Object
     */
    private $riskObj;

    /**
     * Purchase data class object
     *
     * @var Object
     */
    private $purchaseObj;

    /**
     * Configuration object
     *
     * @var Object
     */
    private $configHelper;

    /**
     * Log information
     *
     * @var mixed
     */
    public $logger;

    /**
     * XML validator
     *
     * @var mixed
     */
    private $riskSchemaValidator;

    /**
     * Constructor
     *
     * @param ConfigHelper    $config
     * @param PaymentBuilder  $payment
     * @param RiskBuilder     $risk
     * @param PurchaseBuilder $purchase
     *
     * @return void
     */
    public function __construct(
        ConfigHelper $config,
        PaymentBuilder $payment,
        RiskBuilder $risk,
        PurchaseBuilder $purchase,
        Logger $log,
        XmlValidator $xmlvalidator
    ) {
        $this->configHelper = $config;
        $this->paymentObj = $payment;
        $this->riskObj = $risk;
        $this->purchaseObj = $purchase;
        $this->logger = $log;
        $this->riskSchemaValidator = $xmlvalidator;
    }

    /**
     * Build API required parameters.
     *
     * @param Object $order
     *
     * @return Object self class object
     */
    public function build($order = null)
    {
        //Prepare API params
        $configParams = $this->configHelper->getConfigParams();
        $purchaseParams = $this->purchaseObj->build($order);
        $paymentParams = $this->paymentObj->build($order);
        $riskFilePath = $this->riskObj->writeRiskData($order);
        $riskParam = $this->getRiskData($riskFilePath);
        if (!$riskParam) {
            $this->apiParams = [];
        } else {
            //Including the Configurations, Payment, customer & Risk Information In API Param
            $riskParams['RiskInformation'] = html_entity_decode($riskParam);
            $this->apiParams = array_merge($configParams, $paymentParams, $purchaseParams, $riskParams);
        }

        return $this;
    }

    /**
     * Get API parameters
     *
     * @return Array array of parameters
     */
    public function getApiParams()
    {
        return $this->apiParams;
    }

    /**
     * Verify risk data through schema
     *
     * @param string $riskFile
     *
     * @return mixed
     */
    private function getRiskData($riskFile = null)
    {

        if (!file_exists($riskFile)) {
            $this->logger->info('file ' . $riskFile . 'is not exist');
            return false;
        }
        if (!$this->riskSchemaValidator->validateFeeds($riskFile)) {
            $this->logger->info(print_r($this->riskSchemaValidator->displayErrors(), true));
            return false;
        }
        return file_get_contents($riskFile);
    }
}
