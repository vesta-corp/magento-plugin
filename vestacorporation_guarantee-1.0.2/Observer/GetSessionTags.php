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

/**
 * Get unique session id for current User.
 *
 * @author Chetu Team.
 */
class GetSessionTags implements ObserverInterface
{

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
    public function __construct(ConfigHelper $config)
    {
        $this->configHelper = $config;
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
        // Create a random transaction no to call getSessionTag API
        if ($this->configHelper->isActive()) {
            $this->configHelper->authorise(rand(10000, 9999999));
        }
        return true;
    }
}
