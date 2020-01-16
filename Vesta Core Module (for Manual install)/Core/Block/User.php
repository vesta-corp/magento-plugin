<?php
/**
 * Vesta Payment User Guide block.
 *
 * @author Chetu Team.
 */

namespace Vesta\Core\Block;

use Magento\Framework\View\Element\Template;
use \Magento\Framework\Module\Manager as ModuleManager;

/**
 * Class User
 *
 * @author Chetu Team.
 */
class User extends Template
{
    /**
     * Module manager
     *
     * @var object
     */
    public $moduleManager;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        ModuleManager $_moduleManager,
        array $data = []
    ) {
        $this->moduleManager = $_moduleManager;
        parent::__construct($context, $data);
    }
    
    /**
     * Get Vesta Guarantee active status
     *
     * @return boolean
     */
    public function isVestaGuaranteeActive()
    {
        return $this->moduleManager->isEnabled('Vesta_Guarantee') &&
            $this->moduleManager->isOutputEnabled('Vesta_Guarantee');
    }
    
    /**
     * Get Vesta Payment active status
     *
     * @return boolean
     */
    public function isVestaPaymentActive()
    {
        return $this->moduleManager->isEnabled('Vesta_Payment') &&
            $this->moduleManager->isOutputEnabled('Vesta_Payment');
    }
}
