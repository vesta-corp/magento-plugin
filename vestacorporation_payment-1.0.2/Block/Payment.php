<?php

/**
 * Payment Block File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
namespace Vesta\Payment\Block;

use Vesta\Payment\Model\Ui\PaymentConfigProvider;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Payment Block Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class Payment extends Template
{
    /**
     * @var ConfigProviderInterface
     */
    private $config;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ConfigProviderInterface $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigProviderInterface $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getPaymentConfig()
    {
        $payment = $this->config->getConfig()['payment'];
        $config = $payment[$this->getCode()];
        $config['code'] = $this->getCode();
        return json_encode($config, JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return PaymentConfigProvider::CODE;
    }
}
