<?php

/**
 * CustomerDetailsBuilder File Doc Comment
 *
 * PHP version 7.0
 *
 * @category  Payment
 * @package   Vesta
 * @author    Chetu Team <info@chetu.com>
 * @link      https://chetu.com
 */

namespace Vesta\Payment\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Vesta\Payment\Gateway\Helper\OrderSubjectReader;
use Vesta\Payment\Gateway\Request\RiskDataBuilder;

/**
 * CustomerDetailsBuilder Class Doc Comment
 *
 * PHP version 7.0
 *
 * @category  Payment
 * @package   Vesta
 * @author    Chetu Team <info@chetu.com>
 * @link      https://chetu.com
 */
class CustomerDetailsBuilder implements BuilderInterface
{

    /**
     * Set customer data
     * @var array
     */
    const ADDRESS_ONE = 'AccountHolderAddressLine1';
    const ADDRESS_TWO = 'AccountHolderAddressLine2';
    const CITY = 'AccountHolderCity';
    const COUNTRY_CODE = 'AccountHolderCountryCode';
    const FIRST_NAME = 'AccountHolderFirstName';
    const LAST_NAME = 'AccountHolderLastName';
    const POSTAL_CODE = 'AccountHolderPostalCode';
    const REGION = 'AccountHolderRegion';
    const CREATED_BY = 'CreatedByUser';
    const RISK_DATA = 'RiskInformation';

    /**
     *
     * @var OrderSubjectReader
     */
    private $subjectReader;

    /**
     *
     * @var riskData
     */
    private $riskData;

    /**
     * Constructor
     *
     * @param OrderSubjectReader $subjectReader
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logs,
        OrderSubjectReader $subjectReader,
        RiskDataBuilder $risk
    ) {
        $this->_logs = $logs;
        $this->subjectReader = $subjectReader;
        $this->riskData = $risk;
    }

    /**
     *
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();
        $billingAddress = $order->getBillingAddress();
        return [
            self::ADDRESS_ONE => $billingAddress->getStreetLine1(),
            self::ADDRESS_TWO => ($billingAddress->getStreetLine2() != null) ? $billingAddress->getStreetLine2() : '',
            self::CITY => $billingAddress->getCity(),
            self::COUNTRY_CODE => $billingAddress->getCountryId(),
            self::FIRST_NAME => $billingAddress->getFirstname(),
            self::LAST_NAME => $billingAddress->getLastname(),
            self::POSTAL_CODE => $billingAddress->getPostcode(),
            self::REGION => $billingAddress->getRegionCode(),
            self::CREATED_BY => $billingAddress->getFirstname(). ' '.$billingAddress->getLastname(),
            self::RISK_DATA => (isset($order) && $order!= null)? $this->getRiskXml($order, $payment) : ''
        ];
    }
    
    /**
     * Get Risk information
     * @param object $order
     * @param object $payment
     * @return string risk XML data string
     */

    public function getRiskXml($order, $payment)
    {
        return $this->riskData->prepareRiskXML($order, $payment);
    }
}
