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
 * Get order_cancel_after evevt after order cancelled.
 *
 * @author Chetu Team.
 */
class OrderCancel implements ObserverInterface
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
   * Customer register event handler.
   *
   * @param \Magento\Framework\Event\Observer $observer
   *
   * @return boolean
   */
  public function execute(Observer $observer)
  {
    // Check guarantee module is active
	if ($this->configHelper->isActive()) {
      $order = $observer->getEvent()->getOrder();
      $this->configHelper->getGuaranteeDisposition($order, 'Canceled');
    }
  }
}
