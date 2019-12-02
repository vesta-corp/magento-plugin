<?php

/**
 * VestaHelper File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Helper;

use Magento\Framework\App\Action\Context;
use Vesta\Payment\Model\LogsFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * VestaHelper Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

class VestaHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var  $logFactory;
     */
    protected $logFactory;
    protected $config;
    protected $orderData;
    protected $request;
    private $logs;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Vesta\Payment\Model\LogsFactory $logFactory
     * @param Magento\Framework\App\Config\ScopeConfigInterface $config
     */

    public function __construct(
        Context $_context,
        LogsFactory $_logFactory,
        ScopeConfigInterface $_config,
        OrderInterface $_order,
        \Magento\Framework\App\Request\Http $_request,
        \Psr\Log\LoggerInterface $_logs
    ) {

        $this->logFactory = $_logFactory;
        $this->orderData = $_order;
        $this->config = $_config;
        $this->request = $_request;
        $this->logs = $_logs;
    }

    /**
     * Insert API log into vesta_payment_logs table
     *
     * @param array $data
     */

    public function insertPaymentlog($data = [])
    {
        // insert error log if Debug Log is enable from vesta payment method configuration
        if ($this->config->getValue(
            'payment/vesta_payment/debug_log',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) == 1
            ) {
            try {
                $model = $this->logFactory->create();
                $model->setData($data);
                $model->save();
            } catch (\Exception $ex) {
                throw new \LocalizedException(
                    __('There was an error when inserting the logs: %1.', $ex->getMessage())
                );
            }
        }
    }

    /**
     * Insert payment success logs
     *
     * @param array $data
     */

    public function insertPaymentSuccesslog($data)
    {
        if ($this->config->getValue(
            'payment/vesta_payment/debug_log',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) == 1
        ) {
            try {
                $model = $this->logFactory->create();
                $model = $model->load($data['order_id'], 'order_id');
                if ($model->getOrderId()) {
                    $model->setResponseCode($data['response_code']);
                    $model->setLogContent($data['log_content']);
                } else {
                    $model->setData($data);
                }
                $model->save();
            } catch (\Exception $ex) {
                throw new \LocalizedException(__('There was an error when inserting the logs: %1.', $ex->getMessage()));
            }
        }
    }
    /**
     * get current order id
     *
     * @return int
     */
    public function getCurrentOrderId()
    {
        $orderId = $this->request->getParam('order_id');
        $order = $this->orderData->load($orderId);
        return $order->getIncrementId();
    }
    /**
     * Log error data
     *
     * @param array $data
     * @return boolean
     */
    public function logErrorResponse($data = [])
    {
        $this->logs->log(\Psr\Log\LogLevel::INFO, 'Vesta Payment Error');
        $this->logs->log(
            \Psr\Log\LogLevel::INFO,
            json_encode($data, JSON_PRETTY_PRINT)
        );
        return true;
    }
}
