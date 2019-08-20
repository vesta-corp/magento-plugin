<?php

/**
 * AbstractClientTransaction File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Gateway\Http\Client;

use Vesta\Payment\Model\Adapter\PaymentAdapter;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;

/**
 * AbstractClientTransaction Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
abstract class AbstractClientTransaction implements ClientInterface
{

    /**
     *
     * @var LoggerInterface
     */
    private $logger;
    /**
     *
     * @var Logger
     */
    private $customLogger;
    /**
     *
     * @var PaymentAdapter
     */
    protected $adapter;
    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param Logger          $customLogger
     * @param PaymentAdapter  $transaction
     */
    public function __construct(LoggerInterface $logger, Logger $customLogger, PaymentAdapter $adapter)
    {
        $this->logger = $logger;
        $this->customLogger = $customLogger;
        $this->adapter = $adapter;
    }

    /**
     *
     * @inheritdoc
     */
    public function placeRequest(TransferInterface $transferObject)
    {

        $data = $transferObject->getBody();
        $log = [
            'request' => $data,
            'client' => static::class
        ];
        $response['object'] = [];

        try {
            $response['object'] = $this->process($data);
        } catch (\Exception $e) {
            $message = __($e->getMessage() ?: 'Sorry, but something went wrong');
            $this->logger->critical($message);
            throw new ClientException($message);
        } finally {
            $log['response'] = (array) $response['object'];
            $this->customLogger->debug($log);
        }

        return $response;
    }

    /**
     * Process http request
     *
     * @param  array $data
     * @return \Vesta\Result\Error|\Vesta\Result\Successful
     */
    abstract protected function process(array $data);
}
