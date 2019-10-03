<?php

/**
 * Vesta Fraud protection admin tab.
 *
 * @author Chetu Team.
 */

namespace Vesta\Guarantee\Block\Adminhtml\Order\View\Tab;

/**
 * Vesta Fraud protection  admin tab related functions.
 *
 * @author Chetu Team.
 */
class Vsafe extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'order/view/tab/vsafe.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Vesta Guarantee');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Vesta Guarantee');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {

        return false;
    }

    /**
     * Get Tab Class
     *
     * @return string
     */
    public function getTabClass()
    {

        return 'ajax only';
    }

    /**
     * Get Class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->getTabClass();
    }

    /**
     * Get Tab Url
     *
     * @return string
     */
    public function getTabUrl()
    {

        return $this->getUrl('vsafetab/*/vsafeTab', ['_current' => true]);
    }

    /**
     * Get vesta guarantee status
     *
     * @return string returns status string
     */
    public function getVestaGuaranteeStatus()
    {
        $guaranteeStatus = null;
        $currentOrder = $this->getOrder();
        $guaranteeRes = $currentOrder->getVestaGuaranteeStatus();
        switch ($guaranteeRes) {
            case "0":
                $guaranteeStatus = "Declined";
                break;
            case "1";
                $guaranteeStatus = "Approved";
                break;
            default:
                $guaranteeStatus = "N/A";
                break;
        }

        return $guaranteeStatus;
    }

    /**
     * Get vesta risk index
     *
     * @return integer returns risk index
     */
    public function getVestaRiskProbabilityIndex()
    {
        $currentOrder = $this->getOrder();
        
        return $currentOrder->getVestaGuaranteeResponse();
    }

    /**
     * Get order status
     *
     * @return string order status string
     */
    public function getOrderStatus()
    {
        $currentOrder = $this->getOrder();
        return ($currentOrder->getStatus() == 'holded') ? 'On Hold' : $currentOrder->getStatus();
    }
}
