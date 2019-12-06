<?php

/**
 * Logs Resource Collection
 *
 * @author Chetu Team
 */

namespace Vesta\Guarantee\Model\ResourceModel\Logs;

/**
 * Logs Resource Collection
 *
 * @author Chetu Team
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     *
     * @var string
     */
    protected $_idFieldName = 'log_id';

    protected function _construct()
    {
        $this->_init(
            'Vesta\Guarantee\Model\Logs',
            'Vesta\Guarantee\Model\ResourceModel\Logs'
        );
    }
}
