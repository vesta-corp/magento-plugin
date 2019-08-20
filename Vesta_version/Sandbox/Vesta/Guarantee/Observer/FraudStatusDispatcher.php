<?php

/**
 * Vesta Fraud protection related functions.
 *
 * @author Chetu Team.
 */

namespace Vesta\Guarantee\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Vesta\Guarantee\Services\FraudCaseService;

/**
 * Vesta fraud protection API requests and responses.
 *
 * @author Chetu Team.
 */
class FraudStatusDispatcher implements ObserverInterface
{

    /**
     * Vesta Guarantee service
     *
     * @var Object
     */
    private $caseService;

    /**
     * Vesta Guarantee service
     *
     * @var Object
     */
    private $webSession;

    /**
     * FraudStatusDispatcher constructor.
     *
     * @param FraudCaseService $fraudService
     */
    public function __construct(FraudCaseService $fraudService, SessionManagerInterface $coreSession)
    {
        $this->caseService = $fraudService;
        $this->webSession = $coreSession;
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
        //Triggering event on placing order
        $this->caseService->createCase($observer);
        $this->unSetWebSession();
        
        return true;
    }

    /**
     * Unset web session id.
     *
     * @return bool
     */
    public function unSetWebSession()
    {
        // Unsetting the WebsessionID
        // after each transactions
        $this->webSession->start();
        $this->webSession->unsWebSessionID();
        return true;
    }
}
