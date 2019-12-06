<?php

/**
 * Guarantee Module response.
 *
 * @author Chetu Team.
 */

namespace Vesta\Guarantee\Api\Handler;

use Psr\Log\LoggerInterface as Logger;
use Vesta\Guarantee\Model\Logs;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface as ConfigurationScope;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Message\ManagerInterface;

/**
 * Guarantee Module API response related functions.
 *
 * @author Chetu Team.
 */
class ResponseHandler
{

    /**
     * Log information in log file
     *
     * @var LoggerInterface
     */
    public $logger;

    /**
     * Save Log information in log table
     *
     * @var object
     */
    public $vestalogs;
    
    /**
     * Scope configuration.
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    
    /**
     * Invoice data.
     *
     * @var InvoiceService
     */
    protected $invoiceService;
    
    /**
     * Order data.
     *
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    
    /**
     * Transactional data.
     *
     * @var TransactionFactory
     */
    protected $transactionFactory;
    
    /**
     * Message data.
     *
     * @var messageManager
     */
    protected $messageManager;
    
    /**
     * Message data.
     *
     * @var messageManager
     */
    protected $invoice;

    public function __construct(
        Logger $logger,
        Logs $logs,
        ScopeConfigInterface $scopeInf,
        InvoiceService $invoiceService,
        OrderRepositoryInterface $orderRepository,
        TransactionFactory $transactionFactory,
        ManagerInterface $messageManager
    ) {
        $this->logger = $logger;
        $this->vestalogs = $logs;
        $this->scopeConfig = $scopeInf;
        $this->invoiceService = $invoiceService;
        $this->orderRepository = $orderRepository;
        $this->transactionFactory = $transactionFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * Handle vSafe API Guarantee response
     *
     * @param json   $response
     * @param Object $order
     *
     * @return void
     */
    public function handle($response = null, $order = null)
    {
        $order->getIncrementId();
        $orderId =  $order->getId();
        if (!empty($response)) {
            if (is_array($response) && isset($response['ResponseCode']) && $response['ResponseCode'] == 0) {
                $lowRisk = $this->scopeConfig->getValue(
                    'vesta_protection/general/capture_parameters/capture_lowrisk_payment',
                    ConfigurationScope::SCOPE_STORE
                );
                $mediumRisk = $this->scopeConfig->getValue(
                    'vesta_protection/general/capture_parameters/capture_mediumrisk_payment',
                    ConfigurationScope::SCOPE_STORE
                );
                $highRisk = $this->scopeConfig->getValue(
                    'vesta_protection/general/capture_parameters/cancel_highrisk_order',
                    ConfigurationScope::SCOPE_STORE
                );
                $riskIndex = isset($response['RiskProbabilityIndex']) ? $response['RiskProbabilityIndex'] : '';
                $isPaymentGuaranteeable = isset($response['IsPaymentGuaranteeable']) ?
                    $response['IsPaymentGuaranteeable'] : '';
                $order->setVestaGuaranteeResponse($riskIndex);
                $order->setVestaGuaranteeStatus($isPaymentGuaranteeable);

                // Store the Current Autodisposition configuration along with other response paramters with Order to be 
                // used later once the dispoition call will be made in case of Cancel and Complete Order.
                $autodisposion = $this->scopeConfig->getValue(
                    'vesta_protection/general/autodisposition',
                    ConfigurationScope::SCOPE_STORE
                );
                $response['AutoDisposition'] = $autodisposion;
                $order->setVestaAdditionalInfo(json_encode($response));
                
                if ($riskIndex > 4 && $lowRisk == 1) {
                    $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
                    $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                    $this->generateInvoice($orderId);
                } elseif ($riskIndex >= 3 && $riskIndex <= 4 && $mediumRisk == 1) {
                    $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
                    $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                    $this->generateInvoice($orderId);
                } elseif ($riskIndex < 3 && $highRisk == 1) {
                    $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
                    $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                    $this->generateInvoice($orderId);
                } else {
                    $order->setState(\Magento\Sales\Model\Order::STATE_HOLDED, true);
                    $order->setStatus(\Magento\Sales\Model\Order::STATE_HOLDED);
                }
                $order->addStatusToHistory($order->getStatus(), __("order placed with vesta guarantee"));
                $order->save();
                $this->vestalogs->setResponseData(__("order placed with vesta guarantee"))
                    ->setOrderId($order->getIncrementId())
                    ->save();
            } else {
                $resText = isset($response['ResponseText']) ? $response['ResponseText'] : '';
                $err_res = (is_array($response)) ? $resText : $response;
                $this->vestalogs->setResponseData(__("vesta guarantee api error") . $err_res)
                    ->setOrderId($order->getIncrementId())
                    ->save();
            }
            $this->logger->info(print_r($response, true));
            
            return true;
        } else {
            $this->vestalogs->setResponseData(__("vesta guarantee empty response"))
                ->setOrderId($order->getIncrementId())
                ->save();
            $this->logger->info(print_r($response, true));
            
            return false;
        }
    }
    
    /**
     * Create Invoice Based on Order Object
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function generateInvoice($orderId)
    {
        try {
            $order = $this->orderRepository->get($orderId);
            if (!$order->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The order no longer exists.')
                );
            }
            // check invoice create eligibility
            if (!$order->canInvoice()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The order does not allow an invoice to be created.')
                );
            }
     
            $invoice = $this->invoiceService->prepareInvoice($order);
            $this->invoice = $invoice;
            if (!$this->invoice) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('We can\'t save the invoice right now.')
                );
            }
            if (!$this->invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You can\'t create an invoice without products.')
                );
            }
            $this->invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
            $this->invoice->register();
            $this->invoice->getOrder()->setCustomerNoteNotify(false);
            $this->invoice->getOrder()->setIsInProcess(true);
            $order->addStatusHistoryComment('Automatically INVOICED', false);
            $transactionSave = $this->transactionFactory->create()
                ->addObject($this->invoice)
                ->addObject($invoice->getOrder());
            $transactionSave->save();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
     
            return $this->invoice;
    }
}
