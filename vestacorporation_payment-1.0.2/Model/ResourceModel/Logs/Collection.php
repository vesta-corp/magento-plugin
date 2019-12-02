<?php

/**
 * Logs Collection File Comment doc
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Model\ResourceModel\Logs;

/**
 * Logs Collection Class Comment doc
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     *
     * @return void
     */
    protected $_idFieldName = 'log_id';
    
    protected function _construct()
    {
        $this->_init(
            'Vesta\Payment\Model\Logs',
            'Vesta\Payment\Model\ResourceModel\Logs'
        );
    }
}
