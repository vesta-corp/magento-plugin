<?php

/**
 * Logs Resource Model
 *
 * @author Chetu Team.
 */

namespace Vesta\Guarantee\Model\ResourceModel;

/**
 * Logs Resource Model
 *
 * @author Chetu Team.
 */
class Logs extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('vesta_guarantee_logs', 'log_id');
    }
}
