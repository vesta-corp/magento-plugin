<?php

/**
 * Vesta Payment risk File Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

namespace Vesta\Payment\Gateway\Request;

use \Magento\Catalog\Model\ProductFactory;
use \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use \Magento\Customer\Api\AccountManagementInterface;
use \Magento\Customer\Model\Customer;
use \Magento\Customer\Model\Session;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Psr\Log\LoggerInterface as Logger;
use \Magento\Vault\Api\PaymentTokenManagementInterface;

/**
 * Vesta Payment risk Class Doc Comment
 *
 * PHP version 7.0
 * @category  Payment
 * @package   Vesta Corporation
 * @link      https://trustvesta.com
 * @author    Chetu Team
 */

class RiskDataBuilder
{

    /**
     * Customer object
     *
     * @var mixed
     */
    private $customer;

    /**
     * Session object
     *
     * @var mixed
     */
    private $session;

    /**
     * Order data.
     *
     * @var mixed
     */
    private $order;

    /**
     * Product detail.
     *
     * @var mixed
     */
    private $productFactory;

    /**
     * objectManager
     *
     * @var mixed
     */
    private $objectManager;

    /**
     * Caregory detail.
     *
     * @var mixed
     */
    private $categoryCollectionFactory;

    /**
     * Store data.
     *
     * @var mixed
     */
    private $storeManager;

    /**
     * log.
     *
     * @var string
     */
    private $logger;

    /**
     * Account Information.
     *
     * @var mixed
     */
    private $accInfo;

    /**
     * Checkout session object
     *
     * @var Object
     */
    private $checkoutSession;
    
    /**
     * payment token.
     *
     * @var mixed
     */
    private $paymentToken;

    public function __construct(
        Customer $_customer,
        ProductFactory $_productFactory,
        StoreManagerInterface $_storeManager,
        ObjectManagerInterface $_objectManager,
        CollectionFactory $_categoryCollectionFactory,
        Logger $_logger,
        AccountManagementInterface $_accManger,
        Session $_session,
        \Magento\Checkout\Model\Session $_checkoutSession,
        PaymentTokenManagementInterface $_paymentToken
    ) {
        $this->customer = $_customer;
        $this->productFactory = $_productFactory;
        $this->storeManager = $_storeManager;
        $this->objectManager = $_objectManager;
        $this->categoryCollectionFactory = $_categoryCollectionFactory;
        $this->logger = $_logger;
        $this->accInfo = $_accManger;
        $this->session = $_session;
        $this->checkoutSession = $_checkoutSession;
        $this->paymentToken = $_paymentToken;
    }

    /**
     * Get purchaser information.
     * @return xml string
     */
    public function getPurchaserAcountXML($order = [])
    {
        if ($this->session->isLoggedIn()) {
            $customerId = $order->getCustomerId();
            $customerObj = $this->customer->load($customerId);
            $created = date('Y-m-d\TH:i:s\Z', strtotime($customerObj->getCreatedAt()));
            $dob = date('Y-m-d', strtotime($customerObj->getDob()));
            $confirmEmail = $this->isEmailConfirmed($customerId);
            $email = $customerObj->getEmail();
            $fname = $customerObj->getFirstname();
            $lname = $customerObj->getLastname();
        } else {
            $customerId = '';
            $created = '';
            $dob = '';
            $confirmEmail = 'false';
            $email = $this->getGuestCustomerEmail($order);
            $fname = $this->getGuestCustomerFirstName($order);
            $lname = $this->getGuestCustomerLastName($order);
        }
        $InfoData = "<Purchaser><Account><AccountID>{$customerId}</AccountID>
        <CreatedDTM>{$this->getConvertedTime($created)}</CreatedDTM>
        <DOB>{$dob}</DOB><isEmailVerified>{$confirmEmail}</isEmailVerified>
        <Email>{$email}</Email><FirstName>{$this->replaceSplChar($fname)}</FirstName>
        <LastName>{$this->replaceSplChar($lname)}</LastName>";

        return $InfoData;
    }

    /**
     * Get risk information parameters
     *
     * @return Array parameters
     */
    public function prepareRiskXML($order, $payment)
    {
        
        $billingAdress = $order->getBillingAddress();
    
        $InfoData = "<?xml version='1.0' encoding='UTF-8'?>
                <RiskInformation version='2.0'>
                <Transaction>";

        $street1 = $billingAdress->getStreetLine1();
        $street2 = ($billingAdress->getStreetLine2() != null) ? $billingAdress->getStreetLine2() : '';
        $countryId = $billingAdress->getCountryId();
        $regionName =  $billingAdress->getRegionCode();
        $InfoData .= $this->getPurchaserAcountXML($order);
        $InfoData .= "<AddressLine1>{$this->replaceSplChar($street1)}</AddressLine1>
                    <AddressLine2>{$this->replaceSplChar($street2)}</AddressLine2>
                    <City>{$this->replaceSplChar($billingAdress->getCity())}</City>
                    <CountryCode>{$countryId}</CountryCode>
                    <PostalCode>{$billingAdress->getPostCode()}</PostalCode>
                    <Region>{$regionName}</Region>
                    <PhoneNumber>{$this->getPaddedPhone($billingAdress->getTelephone())}</PhoneNumber>";
        $InfoData .= "</Account>
            </Purchaser>
            <Promotion>
                <Discount></Discount>
                <Code></Code>
            </Promotion>
            <TimeStamp></TimeStamp>
            <MerchantOrderID>{$order->getOrderIncrementId()}</MerchantOrderID>
            <Billing>
                <BillingPhoneNumber>{$this->getPaddedPhone($billingAdress->getTelephone())}</BillingPhoneNumber>
                <Email>{$billingAdress->getEmail()}</Email>
                <PaymentDetails>
                    <isPDOF>{$this->isPdof($order,$payment)}</isPDOF>
					<CardStoredDTM>{$this->getCardStoredDTM($order,$payment)}</CardStoredDTM>
                </PaymentDetails>
            </Billing>";
        $InfoData .= $this->getShippingXml($order);
        $InfoData .= "</Transaction>
                </RiskInformation>";
        //$this->logger->info($InfoData);
        return $InfoData;
    }

    /**
     * get Shipping item related XML.
     *
     * @return XML
     */
    private function getShippingXml($order = null)
    {
        $shippingAdress = $order->getShippingAddress();
        if (!$shippingAdress) {
            $shippingAdress = $order->getBillingAddress();
        }
        $street1 = $shippingAdress->getStreetLine1();
        $street2 = ($shippingAdress->getStreetLine2() != null) ? $shippingAdress->getStreetLine2() : '';
        $shippingCompany = ($shippingAdress != null) ? $shippingAdress->getCompany() : '';
        $shippingFirstName = ($shippingAdress != null) ? $shippingAdress->getFirstname() : '';
        $shippingLastName = ($shippingAdress != null) ? $shippingAdress->getLastname() : '';
        $shippingCity = ($shippingAdress != null) ? $shippingAdress->getCity() : '';
        $shippingRegion = ($shippingAdress != null) ? $shippingAdress->getRegionCode() : '';
        $shippingPostCode = ($shippingAdress != null) ? $shippingAdress->getPostCode() : '';
        $shippingCountryId = ($shippingAdress != null) ? $shippingAdress->getCountryId() : '';
        $shippingTelephone = ($shippingAdress != null) ? $shippingAdress->getTelephone() : '';
        $shippingEmail = ($shippingAdress != null) ? $shippingAdress->getEmail() : '';
        $linesItems = $order->getItems();
        $InfoData = "
            <ShoppingCart DeliveryCount='1'>
                <Delivery LineItemCount='" . count($linesItems) . "'>
                    <DeliveryInfo>
                        <DeliveryMethod></DeliveryMethod>";
        $InfoData .= $this->getShippingCarrier($order);
        $InfoData .= "<ShippingCost></ShippingCost>
                        <Company>{$this->replaceSplChar($shippingCompany)}</Company>
                        <FirstName>{$this->replaceSplChar($shippingFirstName)}</FirstName>
                        <LastName>{$this->replaceSplChar($shippingLastName)}</LastName>
                        <AddressLine1>{$this->replaceSplChar($street1)}</AddressLine1>
                        <AddressLine2>{$this->replaceSplChar($street2)}</AddressLine2>
                        <City>{$this->replaceSplChar($shippingCity)}</City>
                        <Region>{$shippingRegion}</Region>
                        <PostalCode>{$shippingPostCode}</PostalCode>
                        <CountryCode>{$shippingCountryId}</CountryCode>
                        <PhoneNumber>{$this->getPaddedPhone($shippingTelephone)}</PhoneNumber>
                        <Email>{$shippingEmail}</Email>
                    </DeliveryInfo>";
        $InfoData .= $this->getLineItemXml($linesItems, $order);
        $InfoData .= "</Delivery></ShoppingCart><CustomMerchantData
        version='0.1'><CrossBorderFulfillment>
        <ExchangeRate>{$this->getCurrentCurrencyRate()}</ExchangeRate>
        <ReceiveAmount>{$order->getGrandTotalAmount()}</ReceiveAmount>
        <ReceiveCurrency>{$order->getCurrencyCode()}</ReceiveCurrency>
        <SendAmount>{$order->getGrandTotalAmount()}</SendAmount>
        <SendCurrency>{$order->getCurrencyCode()}</SendCurrency>
    </CrossBorderFulfillment></CustomMerchantData>";

        return $InfoData;
    }

    /**
     * get line item related XML.
     * @param mixed $linesItems
     * @return XML
     */
    private function getLineItemXml($linesItems = null)
    {

        $infoData = '';
        foreach ($linesItems as $line) {
            $productId = $line->getProductId();
            $product = $this->loadProductData($productId);
            $infoData .= "<LineItem>
            <ProductCode>{$this->replaceSplChar($line->getSku())}</ProductCode>
            <ProductDescription>{$this->replaceSplChar($line->getName())}</ProductDescription>
            <Quantity>{$line->getQtyOrdered()}</Quantity>
            <UnitPrice>{$line->getPrice()}</UnitPrice>
            <DiverseCart>
                <SKU>{$this->replaceSplChar($line->getSku())}</SKU>
                <ProductType>{$line->getProductType()}</ProductType>";
            $infoData .= $this->getCategoryXml($product);
            $infoData .= " <Brand>{$this->replaceSplChar($product->getManufacturer())}</Brand>
            </DiverseCart>
        </LineItem>";
        }

        return $infoData;
    }

    /**
     * Load product data
     *
     * @param integer $id
     * @return mixed product data
     */
    private function loadProductData($id = null)
    {
        return $this->productFactory->create()->load($id);
    }

    /**
     * get Category related XML.
     *
     * @return XML
     */
    private function getCategoryXml($product)
    {

        $categoryIds = $product->getCategoryIds();
        $infoData = '';
        if (!empty($categoryIds)) {
            $categories = $this->categoryCollectionFactory->create()
                ->addAttributeToSelect(['entity_id', 'name'])
                ->addAttributeToFilter('entity_id', $categoryIds);

            foreach ($categories as $category) {
                $infoData .= "<Category>{$this->replaceSplChar($category->getParentCategory()->getName())}</Category>
							  <SubCategory>{$this->replaceSplChar($category->getName())}</SubCategory>";
            }
        } else {
            $infoData .= "";
        }

        return $infoData;
    }

    /**
     * Get current currency rate
     *
     * @return float
     */
    private function getCurrentCurrencyRate()
    {

        return $this->storeManager->getStore()->getCurrentCurrencyRate();
    }

    /**
     * get Shipping Carrier related XML.
     *
     * @return XML
     */
    private function getShippingCarrier()
    {

        $quote = $this->checkoutSession->getQuote();
        $shipping_method = $quote->getShippingAddress()->getShippingMethod();

        if (preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $shipping_method)) {
            $days = (int)preg_replace('/[^0-9]+/', '', $shipping_method);
            $add = strtotime("+" . $days . " days");
            $expecteddate = date("Y-m-d h:i:sa", $add);
            $infoData = "<ShippingCarrier>{$shipping_method}</ShippingCarrier>
					  <TargetShipDate>{$expecteddate}</TargetShipDate>
					  <ShippingClass>{$shipping_method}</ShippingClass>";
        } else {
            $infoData = "";
        }
        return $infoData;
    }

    /**
     * Check user email is confirmed or not.
     * @param int $customer_id Customer Id.
     * @return bool
     */
    private function isEmailConfirmed($customer_id = null)
    {
        $status = $this->accInfo->getConfirmationStatus($customer_id);
        if ($status == 'account_confirmed') {
            return 'true';
        }
        if ($status == 'account_confirmation_required') {
            return 'false';
        }
    }

    /**
     * Get first name.
     * @return string
     */
    public function getGuestCustomerFirstName($order)
    {
        return $order->getBillingAddress()->getFirstName();
    }
    
    /**
     * Get first name.
     * @return string
     */
    public function getGuestCustomerEmail($order)
    {
        return $order->getBillingAddress()->getEmail();
    }
    
    /**
     * Get last name.
     * @return string
     */
    public function getGuestCustomerLastName($order)
    {
        return $order->getBillingAddress()->getLastName();
    }
    
    /**
     * get storage vault
     *
     * @param $order object
     * @return bool
     */
    public function isPdof($order = null, $payment = null)
    {
        if ($this->session->isLoggedIn()) {
            $customerId = $order->getCustomerId();
            $cclast4 = $payment->getCcLast4();
            $month = $payment->getCcExpMonth();
            $year = $payment->getCcExpYear();
            $expirationDate = $month . "/" . $year;
            $cardList = $this->paymentToken->getListByCustomerId($customerId);
            $flag = 0;
            if (!empty($cardList)) {
                foreach ($cardList as $card) {
                    $data = json_decode($card->getDetails(), true);
                    if ($data['maskedCC'] == $cclast4 && $data['expirationDate'] == $expirationDate &&
                    $card->getPaymentMethodCode() == $payment->getMethod()
                    ) {
                        $flag = 1;
                        break;
                    }
                }
                return $flag;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }
    
    /**
     * Get Card stored date.
     *
     * @return string
     */
    public function getCardStoredDTM($order = null, $payment = null)
    {
        if ($this->session->isLoggedIn()) {
            $customerId = $order->getCustomerId();
            $cclast4 = $payment->getCcLast4();
            $month = $payment->getCcExpMonth();
            $year = $payment->getCcExpYear();
            $expirationDate = $month . "/" . $year;
            $cardList = $this->paymentToken->getListByCustomerId($customerId);
            $CardStoredDTM = "";
            if (!empty($cardList)) {
                foreach ($cardList as $card) {
                    $data = json_decode($card->getDetails(), true);
                    if ($data['maskedCC'] == $cclast4 && $data['expirationDate'] == $expirationDate &&
                    $card->getPaymentMethodCode() == $payment->getMethod()
                    ) {
                        $CardStoredDTM = $card->getCreatedAt();
                    }
                }
                return $CardStoredDTM;
            } else {
                return "";
            }
        } else {
            return "";
        }
    }
    
    /**
     * Get escaping special char.
     * @return string
     */
    public function replaceSplChar($content = null)
    {
        return str_replace(
            ["&", "<", ">", '"', "'", "%"],
            ["and", "&lt;", "&gt;", "&quot;", "&apos;","percent"],
            $content
        );
    }
    
        /**
         * Get padded phone number.
         * @return string
         */
    public function getPaddedPhone($telephone = null)
    {
        if (strlen($telephone) != 15 && strlen($telephone) < 15) {
            return str_pad($telephone, 15, '0', STR_PAD_LEFT);
        } else {
            return $telephone;
        }
    }
    
    /**
     * Get ISO-8601 timestamp.
     * @return timestamp
     */
    public function getConvertedTime($time = null)
    {
        return date('Y-m-d\TH:i:s\Z', strtotime($time));
    }
}
