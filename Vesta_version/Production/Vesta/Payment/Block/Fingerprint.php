<?php

/**
 * Fingerprint File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
namespace Vesta\Payment\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Module\Manager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface as ConfigurationScope;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Fingerprint Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class Fingerprint extends Template
{
    public static $guarantee = "Vesta_Guarantee";
    /**
     * web session
     *
     * @var mixed
     */
    public $webSession;
   
    /**
     * Get configuration details
     *
     * @var mixed
     */
    public $moduleManager;
    
    /**
     * Scope configuration.
     *
     * @var ScopeConfigInterface
     */
    public $scopeConfig;
    
    /**
     * encrypter.
     *
     * @var string
     */
    private $encryptor;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $_context,
        \Magento\Framework\Session\SessionManagerInterface $_session,
        Manager $_moduleM,
        ScopeConfigInterface $_scope,
        EncryptorInterface $_encryptor,
        array $data = []
    ) {
        parent::__construct($_context, $data);
        $this->webSession = $_session;
        $this->moduleManager = $_moduleM;
        $this->scopeConfig = $_scope;
        $this->encryptor = $_encryptor;
    }
    
    /**
     * Get WebSessionID.
     *
     * @return string
     */
    public function getSessionId()
    {
        $this->webSession->start();
        return $this->webSession->getWebSessionID();
    }
    
    /**
     * Get OrgID
     *
     * @return string
     */
    public function getOrgId()
    {
        $this->webSession->start();
        return $this->webSession->getOrgID();
    }
    
    /**
     * Get account name.
     *
     * @return string
     */
    public function getAccountName()
    {
        $accountName = $this->scopeConfig->getValue(
            'payment/vesta_payment/api_username',
            ConfigurationScope::SCOPE_STORE
        );
        return $this->encryptor->decrypt($accountName);
    }
    
    /**
     * Check if webSession is active
     *
     * @return bool
     */
    public function isSession()
    {
        $this->webSession->start();
        if ($this->webSession->getWebSessionID() != null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Whether a module output is permitted by the configuration or not
     *
     * @return boolean
     */
    public function isGuaranteeEnabled()
    {
        return $this->moduleManager->isOutputEnabled(self::$guarantee) &&
                $this->scopeConfig->getValue(
                    'vesta_protection/general/enable',
                    ConfigurationScope::SCOPE_STORE
                );
    }
}
