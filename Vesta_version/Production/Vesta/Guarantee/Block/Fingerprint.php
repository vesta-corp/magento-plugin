<?php
/**
 * Vesta Fraud protection finger printing block.
 *
 * @author Chetu Team.
 */

namespace Vesta\Guarantee\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Encryption\EncryptorInterface;

class Fingerprint extends Template
{

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
        \Magento\Framework\Session\SessionManagerInterface $_session,
        \Vesta\Guarantee\Helper\ConfigHelper $_configHelper,
        \Magento\Framework\Encryption\EncryptorInterface $_encryptor,
        array $data = []
    ) {
        parent::__construct($_context, $data);
        $this->webSession = $_session;
        $this->configHelper = $_configHelper;
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
