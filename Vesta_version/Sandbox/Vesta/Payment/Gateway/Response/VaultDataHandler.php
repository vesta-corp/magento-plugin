<?php

/**
 * VaultDataHandler File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Gateway\Response;

use Vesta\Payment\Gateway\Config\PaymentConfig;
use Vesta\Payment\Gateway\Helper\OrderSubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\Data\PaymentTokenInterfaceFactory;
use Vesta\Payment\Observer\PaymentDataAssignObserver;
use \Magento\Framework\Encryption\EncryptorInterface;

/**
 * VaultDataHandler Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class VaultDataHandler implements HandlerInterface
{
    /**
     * @var PaymentTokenInterfaceFactory
     */
    protected $paymentTokenFactory;

    /**
     * @var OrderPaymentExtensionInterfaceFactory
     */
    protected $paymentExtensionFactory;

    /**
     * @var OrderSubjectReader
     */
    protected $subjectReader;

    /**
     * @var PaymentConfig
     */
    protected $config;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * VaultDataHandler constructor.
     *
     * @param PaymentTokenInterfaceFactory $paymentTokenFactory
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param PaymentConfig $config
     * @param OrderSubjectReader $subjectReader
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @throws \RuntimeException
     */
    public function __construct(
        PaymentTokenInterfaceFactory $paymentTokenFactory,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        PaymentConfig $config,
        OrderSubjectReader $subjectReader,
        \Magento\Framework\Registry $registry,
        \Vesta\Payment\Model\CreditCardData $creditCards,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        EncryptorInterface $_encryptor
    ) {
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->_registry = $registry;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->config = $config;
        $this->subjectReader = $subjectReader;
        $this->creditCards = $creditCards;
        $this->encryptor = $_encryptor;
        $this->serializer = $serializer ? : \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();
        if ((null == $payment->getAdditionalInformation(PaymentDataAssignObserver::PAYMENT_TOKEN_NUM))
            && (0 != $payment->getAdditionalInformation(PaymentDataAssignObserver::PAYMENT_VAULT_ENABLED))) {
            $paymentToken = $this->getVaultPaymentToken($payment, $response['object']);
            if (null !== $paymentToken) {
                $extensionAttributes = $this->getExtensionAttributes($payment);
                $extensionAttributes->setVaultPaymentToken($paymentToken);
            }
        }
    }

    /**
     * Get vault payment token entity
     *
     * @param Transaction $transaction
     * @return PaymentTokenInterface|null
     */
    protected function getVaultPaymentToken($payment, $response)
    {
        if (isset($response['PermanentToken'])) {
            $token = $response['PermanentToken'];
            if (empty($token)) {
                return null;
            }
            $validToken = $this->creditCards->validateToken($token);
            if (null == $validToken) {
                /** @var PaymentTokenInterface $paymentToken */
                $paymentToken = $this->paymentTokenFactory->create();
                $encyptedToken = $this->encryptor->encrypt($token);
                $paymentToken->setGatewayToken($encyptedToken);
                $paymentToken->setIsVisible(1);
                $paymentToken->setExpiresAt($this->getExpirationDate(
                    $payment->getAdditionalInformation(PaymentDataAssignObserver::PAYMENT_CC_EXP_MONTH),
                    $payment->getAdditionalInformation(PaymentDataAssignObserver::PAYMENT_CC_EXP_YEAR)
                ));
                $paymentToken->setTokenDetails($this->convertDetailsToJSON([
                    'type' => $this->_registry->registry(PaymentDataAssignObserver::PAYMENT_CC_TYPE),
                    'maskedCC' => substr($payment->getAdditionalInformation(PaymentDataAssignObserver::PAYMENT_CC_NUM), -4),
                    'expirationDate' => $payment->getAdditionalInformation(
                        PaymentDataAssignObserver::PAYMENT_CC_EXP_MONTH
                    ) . "/"
                        . $payment->getAdditionalInformation(PaymentDataAssignObserver::PAYMENT_CC_EXP_YEAR)
                ]));
                return $paymentToken;
            }
        }
    }

    /**
     * @param Transaction $transaction
     * @return string
     */
    private function getExpirationDate($month = null, $year = null)
    {
        $expDate = new \DateTime($year. '-'.$month. '-'. '01'. ' '. '00:00:00', new \DateTimeZone('UTC'));
        $expDate->add(new \DateInterval('P1M'));
        return $expDate->format('Y-m-d 00:00:00');
    }

    /**
     * Convert payment token details to JSON
     * @param array $details
     * @return string
     */
    private function convertDetailsToJSON($details)
    {
        $json = $this->serializer->serialize($details);
        return $json ? $json : '{}';
    }

    /**
     * Get payment extension attributes
     * @param InfoInterface $payment
     * @return OrderPaymentExtensionInterface
     */
    private function getExtensionAttributes(InfoInterface $payment)
    {
        $extensionAttributes = $payment->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }
}
