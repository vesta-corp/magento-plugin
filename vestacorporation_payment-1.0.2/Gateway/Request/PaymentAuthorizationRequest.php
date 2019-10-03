<?php

/**
 * PaymentAuthorizationRequest File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Gateway\Request;

use Vesta\Payment\Gateway\Config\PaymentConfig;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Vesta\Payment\Gateway\Helper\OrderSubjectReader;

/**
 * PaymentAuthorizationRequest Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class PaymentAuthorizationRequest implements BuilderInterface
{

    /**
     *
     * @var ConfigInterface
     */
    private $config;
    /**
     *
     * @var OrderSubjectReader
     */
    private $subjectReader;
    /**
     *
     * @param ConfigInterface $config
     */
    public function __construct(
        PaymentConfig $config,
        \Psr\Log\LoggerInterface $logs,
        OrderSubjectReader $subjectReader
    ) {
        $this->config = $config;
        $this->_logs = $logs;
        $this->subjectReader = $subjectReader;
    }

    /**
     * Builds ENV request
     *
     * @param  array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        $billingAddress = $order->getBillingAddress();
        $payment = $buildSubject['payment'];
        $order = $payment->getOrder();
        $address = $order->getShippingAddress();
        $request = [];
        return $request;
    }
}
