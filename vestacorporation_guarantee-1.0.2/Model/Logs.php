<?php
/**
 * Logs Model
 */
namespace Vesta\Guarantee\Model;

class Logs extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Vesta\Guarantee\Model\ResourceModel\Logs');
    }
}
