<?php

/**
 * Vesta Fraud protection order view.
 *
 * @author Chetu Team.
 */

namespace Vesta\Guarantee\Block\Adminhtml\Order\View;

/**
 * Vesta Fraud protection order view related functions.
 *
 * @author Chetu Team.
 */
class Info extends \Magento\Sales\Block\Adminhtml\Order\View\Info
{

    /**
     * current registry
     *
     * @var mixed
     */
    protected $registry;

    /**
     * Order repository
     *
     * @var mixed
     */
    protected $order;

    /**
     * system configuration
     *
     * @var mixed
     */
    protected $scopeConfig;

    /**
     * object manager
     *
     * @var obj
     */
    protected $objectManager;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->registry = $registry;
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Current order
     *
     * @return Object $order
     */
    public function currentOrder()
    {
        $this->order = $this->registry->registry('current_order');
        
        return $this->order;
    }
}
