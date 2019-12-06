<?php

/**
 * Guarantee Module get session tags API call.
 *
 * @author Chetu Team.
 */

namespace Vesta\Guarantee\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Vesta\Guarantee\Helper\ConfigHelper;
use \Psr\Log\LoggerInterface as Logger;

/**
 * Get unique session id for current User.
 *
 * @author Chetu Team.
 */
class OrderComplete implements ObserverInterface
{

  /**
   * Log information in log file
   *
   * @var LoggerInterface
   */
  public $logger;

  /**
   * Get configuration details
   *
   * @var mixed
   */
  public $configHelper;


  /**
   * Constructor.
   *
   * @param Logger $logger
   *
   * @return void
   */
  public function __construct(
    ConfigHelper $config,
    Logger $logger
  ) {
    $this->configHelper = $config;
    $this->logger = $logger;
  }

  /**
   * sales_order_save_after event handler.
   *
   * @param \Magento\Framework\Event\Observer $observer
   *
   * @return boolean
   */
  public function execute(Observer $observer)
  {
    // Check Guarantee module is Active
	if ($this->configHelper->isActive()) {
      $order = $observer->getEvent()->getOrder();
	  // Check order status completed
      if ($order->getState() == 'complete') {
        $this->configHelper->getGuaranteeDisposition($order, 'complete');
      }
    }
  }
}
