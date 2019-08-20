<?php
/**
 * Guarantee Module Recent order purchases data.
 *
 * @author Chetu Team.
 */

namespace Vesta\Guarantee\Builder\Request;

/**
 * Guarantee Module Recent order purchases data parameters.
 *
 * @author Chetu Team.
 */
class PurchaseBuilder
{

    /**
     * Order data.
     *
     * @var mixed
     */
    private $order;

    /**
     * Build API Risk parameters.
     *
     * @param Object $order
     *
     * @return array parameters
     */
    public function build($orderArr = null)
    {
        $this->order = $orderArr;
        return $this->getParams();
    }

    /**
     * Get risk information parameters
     *
     * @return Array parameters
     */
    private function getParams()
    {
        $billingAdress = $this->order->getBillingAddress();
        $street = $billingAdress->getStreet();
        $data = [
            "AccountHolderAddressLine1" => isset($street[0]) ? $street[0] : '',
            "AccountHolderAddressLine2" => isset($street[1]) ? $street[1] : '',
            "AccountHolderCity" => $billingAdress->getCity(),
            "AccountHolderCountryCode" => $billingAdress->getCountryId(),
            "AccountHolderFirstName" => $billingAdress->getFirstname(),
            "AccountHolderLastName" => $billingAdress->getLastname(),
            "AccountHolderPostalCode" => $billingAdress->getPostcode(),
            "AccountHolderRegion" => $billingAdress->getRegion(),
            "CreatedByUser" => $billingAdress->getFirstname() . ' ' . $billingAdress->getLastname(),
        ];

        return $data;
    }
}
