<?php

/**
 * ResponseDataValidator File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Framework\Exception\LocalizedException;
use Vesta\Payment\Helper\VestaResponseCodes;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Vesta\Payment\Helper\VestaHelper;

/**
 * ResponseDataValidator Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class ResponseDataValidator extends AbstractValidator
{
    const RESPONSE_CODE = 'ResponseCode';
    const RESPONSE_MESSAGE = 'ResponseText';
    const PAYMENT_STATUS = [10, 52];
    const PAYMENT_RESPONSE = "PaymentStatus";

    /**
     *
     * @var $checkoutSession
     */
    protected $checkoutSession;

    /**
     *
     * @var $helper
     */
    protected $vestaLogsHelper;

    /**
     * Class Constructor
     *
     * @param ResultInterfaceFactory $resultFactory
     * @param CheckoutSession $_checkOutSession
     * @param VestaHelper $_vestaLogs
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        CheckoutSession $_checkOutSession,
        VestaHelper $_vestaLogs
    ) {
        parent::__construct($resultFactory);
        $this->checkoutSession = $_checkOutSession;
        $this->vestaLogsHelper = $_vestaLogs;
    }

    /**
     * Performs validation of result code
     *
     * @param  array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        if (!isset($validationSubject['response']) || !is_array($validationSubject['response']['object'])) {
            throw new LocalizedException(__('Response does not exist.'));
        }

        $response = $validationSubject['response']['object'];

        if ($this->isSuccessfulTransaction($response)) {
            //check if payment status is done.
            if (isset($response[self::RESPONSE_CODE]) && $response[self::RESPONSE_CODE] == "0" &&
                isset($response[self::PAYMENT_RESPONSE]) &&
                in_array($response[self::PAYMENT_RESPONSE], self::PAYMENT_STATUS)
            ) {
                return $this->createResult(true, []);
            } else {
                //insert log on failed.
                $this->responseCapture($response);
                $errMsg = $response[self::RESPONSE_MESSAGE];
                throw new LocalizedException(__($errMsg));
            }
        } else {
            throw new LocalizedException(
                __('Something went wrong please contact your service provider.')
            );
        }
    }

    /**
     * Check if transaction is done
     * @param  array $response
     * @return bool
     */
    private function isSuccessfulTransaction(array $response)
    {
        $returnData = isset($response[self::RESPONSE_CODE]) ? true : false;

        return $returnData;
    }

    /**
     *
     * @param $parseResult
     * @param  $payment
     * @return void
     * @throws LocalizedException
     */
    private function responseCapture($parseResult = [])
    {

        if (isset($parseResult['ResponseCode']) && isset($parseResult['PaymentStatus'])) {
            $responseMsg = VestaResponseCodes::getPaymentCodeText($parseResult['PaymentStatus']);
            $this->saveResponse($parseResult, $parseResult['PaymentStatus'], $responseMsg);
            throw new LocalizedException(
                __('Something went wrong, please contact to your service provider.')
            );
        }

        if (isset($parseResult['ResponseCode']) && isset($parseResult['ResponseText'])) {
            $this->saveResponse($parseResult, $parseResult['ResponseCode'], $parseResult['ResponseText']);
            throw new LocalizedException(
                __('Something went wrong, please contact to your service provider.')
            );
        }
    }

    /**
     * Use to save response
     *
     * @param array $response
     * @param string $code
     * @param string $message
     * @return void
     */
    private function saveResponse($response = null, $code = null, $message = null)
    {
        $orderId = $this->checkoutSession->getQuote()->getReservedOrderId();
        $this->vestaLogsHelper->logErrorResponse($response);
        if ($orderId == null) {
            $orderId = $this->vestaLogsHelper->getCurrentOrderId();
        }
        $data['log_content'] = $message;
        $data['order_id'] = $orderId;
        $data['response_code'] = $code;
        $this->vestaLogsHelper->insertPaymentlog($data);
        return true;
    }
}
