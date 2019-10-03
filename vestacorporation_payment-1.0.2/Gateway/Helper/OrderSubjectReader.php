<?php

/**
 * OrderSubjectReader File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
namespace Vesta\Payment\Gateway\Helper;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

/**
 * OrderSubjectReader Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class OrderSubjectReader
{

    /**
     * Reads response object from subject
     *
     * @param  array $subject
     * @return object
     */
    public function readResponseObject(array $subject)
    {
        $response = SubjectReader::readResponse($subject);
        if (!isset($response['object']) || !is_object($response['object'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Response object does not exist.'));
        }

        return $response['object'];
    }

    /**
     * Reads payment from subject
     *
     * @param  array $subject
     * @return PaymentDataObjectInterface
     */
    public function readPayment(array $subject)
    {
        return SubjectReader::readPayment($subject);
    }

    /**
     * Reads amount from subject
     *
     * @param  array $subject
     * @return mixed
     */
    public function readAmount(array $subject)
    {
        return SubjectReader::readAmount($subject);
    }

    /**
     * Reads customer id from subject
     *
     * @param  array $subject
     * @return int
     */
    public function readCustomerId(array $subject)
    {
        if (!isset($subject['customer_id'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The "customerId" field does not exists.'));
        }

        return (int) $subject['customer_id'];
    }
}
