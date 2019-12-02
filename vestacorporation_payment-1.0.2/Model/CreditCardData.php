<?php

/**
 * CreditCardData File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Model;

/**
 * CreditCardData Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class CreditCardData
{
    const PAYMENT_METHOC_CODE = 'payment_method_code';
    const IS_ACTIVE = 'is_active';
    const IS_VISIBLE = 'is_visible';
    const CUSTOMER_ID = 'customer_id';
    const PUBLIC_HASH = 'public_hash';
    const GATEWAY_TOKEN = 'gateway_token';
    const CODE = 'vesta_payment';
    const FETCH_CARD_LIMIT = 1;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @var \Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory
     */
    private $tokenCollection;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logs;

    /**
     * @var \Vesta\Payment\Gateway\Config\PaymentConfig
     */
    private $config;

    /**
     * @var string
     */
    private $encryptor;

    /**
     * @param \Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory $tokenCollection
     * @param \Magento\Customer\Model\Session $session
     * @param \Psr\Log\LoggerInterface $logs
     * @param \Vesta\Payment\Gateway\Config\PaymentConfig $config
     */
    public function __construct(
        \Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory $_tokenCollection,
        \Magento\Customer\Model\Session $_session,
        \Psr\Log\LoggerInterface $_logs,
        \Vesta\Payment\Gateway\Config\PaymentConfig $_config,
        \Magento\Framework\Encryption\EncryptorInterface $_encryptor
    ) {
        $this->tokenCollection = $_tokenCollection->create();
        $this->session = $_session;
        $this->logs = $_logs;
        $this->config = $_config;
        $this->encryptor = $_encryptor;
    }

    /**
     * Get customer credit card data
     *
     * @param null
     * @return array credit card data Array
     */
    public function getCurrentCustomerCCardByCartId($cartId = null)
    {
        if (isset($cartId)) {
            return $this->tokenCollection
                ->addFieldToFilter(self::IS_ACTIVE, 1)
                ->addFieldToFilter(self::IS_VISIBLE, 1)
                ->addFieldToFilter(self::PAYMENT_METHOC_CODE, self::CODE)
                ->addFieldToFilter(self::PUBLIC_HASH, $cartId)
                ->addFieldToFilter(self::CUSTOMER_ID, $this->getLoggedInCustomer())
                ->getFirstItem();
        } else {
            $this->logs->log(\Psr\Log\LogLevel::INFO, "Cart Id can not be pass blank into " . __FUNCTION__);
        }
    }
    /**
     * Validate Token
     *
     * @param string $token
     * @return void
     */
    public function validateToken($token = null)
    {
        $token = $this->encryptor->encrypt($token);
        $tokenCollection = $this->tokenCollection
            ->addFieldToFilter(self::IS_ACTIVE, 1)
            ->addFieldToFilter(self::IS_VISIBLE, 1)
            ->addFieldToFilter(self::PAYMENT_METHOC_CODE, self::CODE)
            ->addFieldToFilter(self::GATEWAY_TOKEN, $token)
            ->addFieldToFilter(self::CUSTOMER_ID, $this->getLoggedInCustomer())
            ->getFirstItem();
        return null != $tokenCollection->getEntityId() ? true : false;
    }

    /**
     * Get customer single credit card data
     *
     * @param null
     * @return array single credit card data Array
     */
    public function getCurrentCustomerAllCreditCards()
    {
        return $this->tokenCollection
            ->addFieldToFilter(self::IS_ACTIVE, 1)
            ->addFieldToFilter(self::IS_VISIBLE, 1)
            ->addFieldToFilter(self::PAYMENT_METHOC_CODE, self::CODE)
            ->addFieldToFilter(self::CUSTOMER_ID, $this->getLoggedInCustomer());
    }

    /**
     * Get get logged customer Id
     *
     * @param null
     * @return integer logged customer Id Int
     */
    private function getLoggedInCustomer()
    {
        return $this->session->getCustomerId();
    }
}
