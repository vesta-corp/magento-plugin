<?php

/**
 * PaymentConfigProvider File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Vesta\Payment\Gateway\Config\PaymentConfig;

/**
 * PaymentConfigProvider Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
final class PaymentConfigProvider implements ConfigProviderInterface
{

    const CODE = 'vesta_payment';
    const CC_VAULT_CODE = 'vesta_cc_vault';

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
     * @var \Vesta\Payment\Model\CreditCardData
     */
    private $creditCards;

    /**
     * @var \Vesta\Payment\Gateway\Config\PaymentConfig
     */
    private $config;

    /**
     * @param \Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory $tokenCollection
     * @param \Magento\Customer\Model\Session $session
     * @param \Psr\Log\LoggerInterface $logs
     * @param \Vesta\Payment\Model\CreditCardData $creditCards
     * @param \Vesta\Payment\Gateway\Config\PaymentConfig $config
     */
    public function __construct(
        \Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory $tokenCollection,
        \Magento\Customer\Model\Session $session,
        \Psr\Log\LoggerInterface $logs,
        \Vesta\Payment\Model\CreditCardData $creditCards,
        PaymentConfig $config
    ) {
        $this->tokenCollection = $tokenCollection->create();
        $this->session = $session;
        $this->logs = $logs;
        $this->creditCards = $creditCards;
        $this->config = $config;
    }
    
    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {

        $cards = $this->creditCards->getCurrentCustomerAllCreditCards();
        /** @var \Magento\Vault\Model\Card $card */
        foreach ($cards as $card) {
            $maskedCardValue = json_decode($card->getDetails());
            $creditCardType = $maskedCardValue->type;
            $storedCardOptions[] = [
                'id' => $card->getPublicHash(),
                'label' => $creditCardType . " XXXX-" . $maskedCardValue->maskedCC,
                'selected' => false,
                'type' => $maskedCardValue->type,
            ];

            $selected = $card->getPublicHash();
        }

        $storedCardOptions = empty($storedCardOptions) ? "" : $storedCardOptions;
        $selected = empty($selected) ? "" : $selected;
        return [
            'payment' => [
                self::CODE => [
                    'storedCards' => $storedCardOptions,
                    'selectedCard' => $selected,
                    'saveCard' => $this->config->getCardSaveStatus(),
                    'tokenApi' => $this->config->getVestaTokenAPI(),
                    'merchantAccountName' => $this->config->getMerchantAccountId(),
                ]
            ]
        ];
    }
}
