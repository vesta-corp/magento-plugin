<?php

/**
 * Vesta Logs File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
 
namespace Vesta\Payment\Model;

/**
 * Vesta Logs Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */
class Logs extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Vesta\Payment\Model\ResourceModel\Logs');
    }
}
