<?php

/**
 * Vesta Guarantee in payment Comment doc
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Model;

use \Magento\Config\Model\Config\CommentInterface;
use \Magento\Framework\Module\Manager;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\ScopeInterface as ConfigurationScope;
use \Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Vesta Guarantee in payment Comment class doc
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

class Comment implements CommentInterface
{
     /**
      * moduleManager object
      *
      * @var mixed
      */
    protected $moduleManager;
    
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;
    
    /**
     * Scope configuration.
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
         
    public function __construct(
        ScopeConfigInterface $_scopeInf,
        Manager $_moduleManager,
        WriterInterface $_configWriter
    ) {
        $this->scopeConfig = $_scopeInf;
        $this->moduleManager = $_moduleManager;
        $this->configWriter = $_configWriter;
    }
    
    /**
     * Dynamic comment
     *
     * @var String
     */
    public function getCommentText($elementValue)
    {
        
        if ($this->moduleManager->isOutputEnabled('Vesta_Guarantee')) {
            if ($this->scopeConfig->getValue('vesta_protection/general/enable', ConfigurationScope::SCOPE_STORE)) {
                $comment = "<b>Note: Vesta Guarantee extension is enabled in this application.</b>";
            } else {
                $comment = "<b>Note: Vesta Guarantee extension is available in this application but not enabled.</b>";
                $comment .= "<style>
                #payment_us_vesta_payment_vesta_fraudprotection_vesta_fraudprotection_active_inherit,
                label
                [for=payment_us_vesta_payment_vesta_fraudprotection_vesta_fraudprotection_active_inherit]
                {display:none;}</style>";
                $this->configWriter->save(
                    'payment/vesta_payment/vesta_fraudprotection_active',
                    0,
                    $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                );
            }
        } else {
            $comment = "<b>Note: Vesta Guarantee extension is not available in this application. 
            Please install extension from </b>
            <a href='https://marketplace.magento.com/extensions.html' 
            target='_blank'>https://marketplace.magento.com/extensions.html</a>";
            $comment .=  "<style>
            #payment_us_vesta_payment_vesta_fraudprotection_vesta_fraudprotection_active_inherit,
            label[for=payment_us_vesta_payment_vesta_fraudprotection_vesta_fraudprotection_active_inherit]
            {display:none;}</style>";
            $this->configWriter->save(
                'payment/vesta_payment/vesta_fraudprotection_active',
                0,
                $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            );
        }
        
        return $comment;
    }
}
