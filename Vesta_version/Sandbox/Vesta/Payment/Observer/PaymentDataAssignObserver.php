<?php

/**
 * PaymentDataAssignObserver File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use \Magento\Framework\Exception\LocalizedException;

/**
 * PaymentDataAssignObserver Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class PaymentDataAssignObserver extends AbstractDataAssignObserver
{

    const PAYMENT_CARD_TOKEN = 'payment_token';
    const PAYMENT_CC_NUM = 'cc_number';
    const PAYMENT_CC_EXP_MONTH = 'cc_exp_month';
    const PAYMENT_CC_EXP_YEAR = 'cc_exp_year';
    const PAYMENT_CC_TYPE = 'cc_type';
    const PAYMENT_CVV = 'cc_cid';
    const PAYMENT_VAULT_ENABLED = 'vault_is_enabled';
    const PAYMENT_METHOC_CODE = 'payment_method_code';
    const IS_ACTIVE = 'is_active';
    const IS_VISIBLE = 'is_visible';
    const CUSTOMER_ID = 'customer_id';
    const PUBLIC_HASH = 'public_hash';
    const PAYMENT_TOKEN_NUM = 'gateway_token';
    
    /**
     * @var mixed
     */
    private $encryptor;
    private $registry;
    private $session;
    private $creditCards;

    /**
     *
     * @var array
     */
    private $additionalInformationList = [
        self::PAYMENT_CARD_TOKEN,
        self::PAYMENT_CC_EXP_MONTH,
        self::PAYMENT_CC_EXP_YEAR,
        self::PAYMENT_CVV,
        self::PAYMENT_TOKEN_NUM,
        self::PAYMENT_CC_NUM,
        self::PAYMENT_VAULT_ENABLED
    ];

    /**
     *
     * @param  Observer $observer
     * @return void
     */
    public function __construct(
        \Magento\Customer\Model\Session $_session,
        \Magento\Framework\Registry $_registry,
        \Vesta\Payment\Model\CreditCardData $_creditCards,
        \Magento\Framework\Encryption\EncryptorInterface $_encryptor
    ) {
        $this->registry = $_registry;
        $this->session = $_session;
        $this->creditCards = $_creditCards;
        $this->encryptor = $_encryptor;
    }

    /**
     * Observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }
        $this->paymentInfo = $this->readPaymentModelArgument($observer);
        if (isset($additionalData['card_id']) && null != $additionalData['card_id']) {
            $this->loadCardData($additionalData);
        } else {
            if (isset($additionalData["vault_is_enabled"])) {
                $this->paymentInfo->setAdditionalInformation(
                    self::PAYMENT_VAULT_ENABLED,
                    $additionalData["vault_is_enabled"]
                );
            } else {
                $this->paymentInfo->setAdditionalInformation(
                    self::PAYMENT_VAULT_ENABLED,
                    0
                );
            }
            $this->registry->register(self::PAYMENT_CC_TYPE, $additionalData["cc_type"]);
            $this->saveCardData($additionalData);
        }
    }

    /**
     * Load Card Data
     *
     * @param object $additionalData
     * @return void
     */
    private function saveCardData($additionalData = null)
    {
        foreach ($this->additionalInformationList as $additionalInformationKey) {
            if (isset($additionalData[$additionalInformationKey])) {
                if (null != $additionalData[$additionalInformationKey]) {
                    $this->paymentInfo->setAdditionalInformation(
                        $additionalInformationKey,
                        $additionalData[$additionalInformationKey]
                    );
                } else {
                    throw new LocalizedException(
                        __('Validation Error : Mandatory fields can not be blank.')
                    );
                }
            }
        }
    }

    /**
     * Set selected card data
     *
     * @param object $additionalData
     * @return void
     */
    private function loadCardData($additionalData)
    {
        $cardData = $this->creditCards->getCurrentCustomerCCardByCartId($additionalData['card_id']);
        if (null != $cardData->getGatewayToken()) {
            $details = json_decode($cardData->getDetails());
            $expirationDateArr = explode("/", $details->expirationDate);
            $expirationMonth = current($expirationDateArr);
            $expirationYear = end($expirationDateArr);
            //set value in payment method for backend and frontend
            $this->registry->register(self::PAYMENT_CC_TYPE, $details->type);
            $token = $this->encryptor->decrypt($cardData->getGatewayToken());
            $maskedCC = $details->maskedCC;
            $expirationMonth = $expirationMonth;
            $expirationYear = $expirationYear;
            $detailsType = $details->type;
            $this->paymentInfo->setAdditionalInformation(self::PAYMENT_TOKEN_NUM, $token);
            $this->paymentInfo->setAdditionalInformation(self::PAYMENT_CC_NUM, $maskedCC);
            $this->paymentInfo->setAdditionalInformation(self::PAYMENT_CC_EXP_MONTH, $expirationMonth);
            $this->paymentInfo->setAdditionalInformation(self::PAYMENT_CC_EXP_YEAR, $expirationYear);
            $this->paymentInfo->setAdditionalInformation(self::PAYMENT_CC_TYPE, $detailsType);
        } else {
            throw new LocalizedException(
                __('You have not stored credit card anymore. Please choose add new card option.')
            );
        }
    }
}
