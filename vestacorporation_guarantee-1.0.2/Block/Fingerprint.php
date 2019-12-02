<?php
/**
 * Vesta Fraud protection finger printing block.
 *
 * @author Chetu Team.
 */

namespace Vesta\Guarantee\Block;

use Magento\Framework\View\Element\Template;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\ScopeInterface as ConfigurationScope;

class Fingerprint extends Template
{

    /**
     * Scope configuration.
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Environment Type.
     *
     * @var string
     */
    private $environmentType;
    
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
    public $configHelper;
    
    /**
     * encrypter.
     *
     * @var string
     */
    protected $encryptor;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $_context,
        ScopeConfigInterface $_scopeInf,
        \Magento\Framework\Session\SessionManagerInterface $_session,
        \Vesta\Guarantee\Helper\ConfigHelper $_configHelper,
        \Magento\Framework\Encryption\EncryptorInterface $_encryptor,
        array $data = []
    ) {
        parent::__construct($_context, $data);
        $this->scopeConfig = $_scopeInf;
        $this->webSession = $_session;
        $this->configHelper = $_configHelper;
        $this->encryptor = $_encryptor;
    }

    /**
     * Get DataCollector URL.
     *
     * @return string
     */
    public function getDCURL()
    {
        $this->environmentType = $this->scopeConfig->getValue(
                'vesta_protection/general/environment_type', 
                ConfigurationScope::SCOPE_STORE
        );
        if ($this->environmentType == 'sandbox') {
            $dc_api_url = $this->scopeConfig->getValue(
                'vesta_protection/general/sandbox_datacollector_url', 
                ConfigurationScope::SCOPE_STORE
            );
            $dc_api_url = rtrim($dc_api_url, "/") . '/';
            return $dc_api_url.'fetch/an/SandboxID1286';
        } else {
            $accountName =  $this->getAccountName();
            $dc_api_url = $this->scopeConfig->getValue(
                'vesta_protection/general/production_datacollector_url', 
                ConfigurationScope::SCOPE_STORE
            );
            $dc_api_url = rtrim($dc_api_url, "/") . '/';
            return $dc_api_url.'fetch/an/'.$accountName;
        }
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
        return $this->webSession->getOrgID();
    }
    
    /**
     * Get account name.
     *
     * @return string
     */
    public function getAccountName()
    {
        $accountName = $this->configHelper->userName;
        return $this->encryptor->decrypt($accountName);
    }
    
    /**
     * Check if webSession is active
     *
     * @return bool
     */
    public function isSession()
    {
        if ($this->configHelper->webSession->getWebSessionID() != null) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Checks if module is enabled.
     *
     * @return boolean
     */
    public function isModuleActive()
    {
        return $this->configHelper->isActive();
    }
}
