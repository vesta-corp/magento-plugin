<?php

/**
 * Vesta Fraud protection risk related data.
 *
 * @author Chetu Team.
 */

namespace Vesta\Guarantee\Builder\Request;

use Vesta\Guarantee\Helper\ConfigHelper;
use \Magento\Catalog\Model\ProductFactory;
use \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use \Magento\Customer\Api\AccountManagementInterface;
use \Magento\Customer\Model\Customer;
use \Magento\Customer\Model\Session;
use \Magento\Framework\Filesystem\DirectoryList;
use \Magento\Framework\Module\Manager;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Psr\Log\LoggerInterface as Logger;
use \Magento\Directory\Model\Region;

/**
 * Vesta fraud protection API risk information parameters.
 *
 * @author Chetu Team.
 */
class RiskBuilder
{

    /**
     * Customer object
     *
     * @var mixed
     */
    protected $customer;

    /**
     * Session object
     *
     * @var mixed
     */
    protected $session;

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
     * moduleManager object
     *
     * @var mixed
     */
    protected $moduleManager;

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
    protected $categoryCollectionFactory;

    /**
     * Store data.
     *
     * @var mixed
     */
    protected $storeManager;

    /**
     * Module directory.
     *
     * @var string
     */
    protected $directory;

    /**
     * log.
     *
     * @var string
     */
    protected $logger;

    /**
     * Account Information.
     *
     * @var mixed
     */
    private $accInfo;

    /**
     * Configuration object
     *
     * @var Object
     */
    private $configHelper;
    
    /**
     * directory object
     *
     * @var Object
     */
    protected $regionDirectory;

    public function __construct(
        Customer $_customer,
        ProductFactory $_productFactory,
        StoreManagerInterface $_storeManager,
        Manager $_moduleManager,
        ObjectManagerInterface $_objectManager,
        CollectionFactory $_categoryCollectionFactory,
        Logger $_logger,
        AccountManagementInterface $_accManger,
        ConfigHelper $_config,
        Session $_session,
        DirectoryList $_dirList,
        Region $_region
    ) {
        $this->customer = $_customer;
        $this->productFactory = $_productFactory;
        $this->storeManager = $_storeManager;
        $this->moduleManager = $_moduleManager;
        $this->objectManager = $_objectManager;
        $this->categoryCollectionFactory = $_categoryCollectionFactory;
        $this->directory = $_dirList;
        $this->logger = $_logger;
        $this->accInfo = $_accManger;
        $this->configHelper = $_config;
        $this->session = $_session;
        $this->regionDirectory = $_region;
    }

    /**
     * Build API Risk parameters.
     *
     * @param Object $order
     *
     * @return array parameters
     */
    public function writeRiskData($orderArr = null)
    {
        $this->order = $orderArr;
        return $this->prepareRiskXML();
    }

    /**
     * Get purchaser information.
     *
     * @return xml string
     */
    public function getPurchaserAcountXML()
    {
        if ($this->session->isLoggedIn()) {
            $customerId = $this->order->getCustomerId();
            $customerObj = $this->customer->load($customerId);
            $created = date('Y-m-d\TH:i:s\Z', strtotime($customerObj->getCreatedAt()));
            $dob = date('Y-m-d', strtotime($customerObj->getDob()));
            $confirmEmail = $this->isEmailConfirmed($customerId);
            $email = $customerObj->getEmail();
            $fname = $customerObj->getFirstname();
            $lname = $customerObj->getLastname();
        } else {
            $customerId = '';
            $created = date('Y-m-d\TH:i:s\Z');
            $dob = '1970-01-01';
            $confirmEmail = 'false';
            $email = $this->order->getCustomerEmail();
            $fname = $this->getGuestCustomerFirstName($this->order);
            $lname = $this->getGuestCustomerLastName($this->order);
        }
        $InfoData = "<Account><AccountID>{$customerId}</AccountID>
		<CreatedDTM>{$this->getConvertedTime($created)}</CreatedDTM><DOB>{$dob}</DOB>
		<isEmailVerified>{$confirmEmail}</isEmailVerified><Email>{$email}</Email>
		<FirstName>{$this->replaceSplChar($fname)}</FirstName><LastName>{$this->replaceSplChar($lname)}</LastName>";

        return $InfoData;
    }

    /**
     * Get risk information parameters
     *
     * @return Array parameters
     */
    private function prepareRiskXML()
    {
        $billingAdress = $this->order->getBillingAddress();
        $countryId = $billingAdress->getCountryId();
        $regionName =  $billingAdress->getRegion();
        $discountAmt =  str_replace("-", "", $this->order->getDiscountAmount());
        $region = ($countryId != 'US') ? $regionName : $this->getRegionCode($countryId, $regionName);
        $InfoData = "<?xml version='1.0' encoding='UTF-8'?>
                <RiskInformation version='2.0'>
                <Transaction>
                <Purchaser>";
        if($this->session->isLoggedIn()){
        $street = $billingAdress->getStreet();
        $street1 = isset($street[0]) ? $street[0] : '';
        $street2 = isset($street[1]) ? $street[1] : '';
        $InfoData .= $this->getPurchaserAcountXML();
        $InfoData .= "<AddressLine1>{$this->replaceSplChar($street1)}</AddressLine1>
                    <AddressLine2>{$this->replaceSplChar($street2)}</AddressLine2>
                    <City>{$this->replaceSplChar($billingAdress->getCity())}</City>
                    <CountryCode>{$countryId}</CountryCode>
                    <PostalCode>{$billingAdress->getPostCode()}</PostalCode>
                    <Region>{$region}</Region>
                    <PhoneNumber>{$this->getPaddedPhone($billingAdress->getTelephone())}</PhoneNumber>";
        $InfoData .= $this->getSocialNetworkXml();
        $InfoData .= "</Account>";
        }
        $InfoData .= "</Purchaser>
            <Promotion>
                <Discount>{$discountAmt}</Discount>
                <Code>{$this->replaceSplChar($this->order->getCouponCode())}</Code>
            </Promotion>
            <TimeStamp>{$this->getConvertedTime($this->order->getCreatedAt())}</TimeStamp>
            <MerchantOrderID>{$this->order->getIncrementId()}</MerchantOrderID>
            <Billing>
                <BillingPhoneNumber>{$this->getPaddedPhone($billingAdress->getTelephone())}</BillingPhoneNumber>
                <Email>{$billingAdress->getEmail()}</Email>
                <PaymentDetails>
                    <isPDOF>{$this->configHelper->isPdof($this->order)}</isPDOF>
		    <CardStoredDTM>
			{$this->getConvertedTime($this->configHelper->getCardStoredDTM($this->order))}
			</CardStoredDTM>
                </PaymentDetails>
            </Billing>";
        $InfoData .= $this->getShippingXml();
        $InfoData .= "</Transaction>
                </RiskInformation>";
        //$this->logger->info($InfoData);
        return $this->writeFile($InfoData);
    }

    /**
     * get Shipping item related XML.
     *
     * @return XML
     */
    private function getShippingXml()
    {
        $shippingAdress = $this->order->getShippingAddress();
        
        $street = ($shippingAdress != null) ? $shippingAdress->getStreet() : [];
        $street1 = isset($street[0]) ? $street[0] : '';
        $street2 = isset($street[1]) ? $street[1] : '';
        $shippingCompany = ($shippingAdress != null) ? $shippingAdress->getCompany() : '';
        $shippingFirstName = ($shippingAdress != null) ? $shippingAdress->getFirstname() : '';
        $shippingLastName = ($shippingAdress != null) ? $shippingAdress->getLastname() : '';
        $shippingCity = ($shippingAdress != null) ? $shippingAdress->getCity() : '';
        $shippingRegion = ($shippingAdress != null) ? $shippingAdress->getRegion() : '';
        $shippingPostCode = ($shippingAdress != null) ? $shippingAdress->getPostCode() : '';
        $shippingCountryId = ($shippingAdress != null) ? $shippingAdress->getCountryId() : '';
        $shippingTelephone = ($shippingAdress != null) ? $shippingAdress->getTelephone() : '';
        $shippingEmail = ($shippingAdress != null) ? $shippingAdress->getEmail() : '';
        $region = ($shippingCountryId != 'US') ? $shippingRegion :
            $this->getRegionCode($shippingCountryId, $shippingRegion);

        $linesItems = $this->order->getAllVisibleItems();
        $InfoData = "
            <ShoppingCart DeliveryCount='1'>
                <Delivery LineItemCount='" . count($linesItems) . "'>
                    <DeliveryInfo>
                        <DeliveryMethod>{$this->order->getShippingMethod()}</DeliveryMethod>";
        $InfoData .= $this->getShippingCarrier();
        $InfoData .= "<ShippingCost>{$this->order->getShippingAmount()}</ShippingCost>
                        <Company>{$this->replaceSplChar($shippingCompany)}</Company>
                        <FirstName>{$this->replaceSplChar($shippingFirstName)}</FirstName>
                        <LastName>{$this->replaceSplChar($shippingLastName)}</LastName>
                        <AddressLine1>{$this->replaceSplChar($street1)}</AddressLine1>
                        <AddressLine2>{$this->replaceSplChar($street2)}</AddressLine2>
                        <City>{$this->replaceSplChar($shippingCity)}</City>
                        <Region>{$this->replaceSplChar($region)}</Region>
                        <PostalCode>{$shippingPostCode}</PostalCode>
                        <CountryCode>{$shippingCountryId}</CountryCode>
                        <PhoneNumber>{$this->getPaddedPhone($shippingTelephone)}</PhoneNumber>
                        <Email>{$shippingEmail}</Email>
                    </DeliveryInfo>";
        $InfoData .= $this->getLineItemXml($linesItems);
        $InfoData .= "</Delivery></ShoppingCart>";

        return $InfoData;
    }

    /**
     * get line item related XML.
     *
     * @param  mixed $linesItems
     * @return XML
     */
    private function getLineItemXml($linesItems = null)
    {
        $infoData = '';
        foreach ($linesItems as $line) {
            $iname = $line->getName();
            $dic = $line->getDiscountAmount();
            $productId = $line->getProductId();
            $product = $this->productFactory->create()->load($productId);
            $total = $line->getPrice() - $line->getDiscountAmount();
            $infoData .= "<LineItem>
            <ProductCode>{$this->replaceSplChar($line->getSku())}</ProductCode>
            <ProductDescription>{$this->replaceSplChar($line->getName())}</ProductDescription>
            <Quantity>{$line->getQtyOrdered()}</Quantity>
            <UnitPrice>{$line->getPrice()}</UnitPrice>
            <DiverseCart>
                <SKU>{$this->replaceSplChar($line->getSku())}</SKU>
                <ProductType>{$line->getProductType()}</ProductType>";
            $infoData .= $this->getCategoryXml($product);
            $infoData .= " <Brand>{$this->replaceSplChar($product->getAttributeText('manufacturer'))}</Brand>
            </DiverseCart>
        </LineItem>";
        }

        return $infoData;
    }

    /**
     * get Social Network related XML.
     *
     * @return XML
     */
    private function getSocialNetworkXml()
    {
        if ($this->session->isLoggedIn()) {
            $customerId = $this->order->getCustomerId();
            $customerObj = $this->customer->load($customerId);
            $infoData = '';

            if ($this->moduleManager->isOutputEnabled('Mageplaza_SocialLogin')) {
                $socialData = $this->objectManager->create('\Mageplaza\SocialLogin\Model\Social')->getCollection()
                    ->addFieldToSelect(['customer_id', 'type', 'social_id'])
                    ->addFieldToFilter('customer_id', $customerId);

                foreach ($socialData as $social) {
                    $infoData .= "<SocialNetwork>
							<Email>{$customerObj->getEmail()}</Email>
							<AccountID>{$social->getSocialId()}</AccountID>
							<Platform>{$social->getType()}</Platform>
							</SocialNetwork>";
                }
            } else {
                $infoData .= "";
            }

            return $infoData;
        } else {
            return "";
        }
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
     * Get current store currency code
     *
     * @return string
     */
    private function getCurrentCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
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

        $shipping_method = $this->order->getShippingMethod();

        /*if (preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $shipping_method)) {
            $orderDate = $this->order->getCreatedAt();
            $days = intval(preg_replace('/[^0-9]+/', '', $shipping_method), 10);
            $add = strtotime("+" . $days . " days");
            $expecteddate = date("Y-m-d h:i:sa", $add);

            $infoData = "<ShippingCarrier>{$shipping_method}</ShippingCarrier>
					  <TargetShipDate>{$expecteddate}</TargetShipDate>
					  <ShippingClass>{$shipping_method}</ShippingClass>";
        } else {
            $infoData = "";
        }*/
        $get_shipping = explode('_', $shipping_method);
        $infoData = "<ShippingCarrier>{$get_shipping[0]}</ShippingCarrier>
        <ShippingClass>{$get_shipping[1]}</ShippingClass>";
        return $infoData;
    }

    /**
     * write xml file
     *
     * @param  string $data
     * @return mixed
     */
    private function writeFile($data = null)
    {
        try {
            $riskFile = $this->order->getIncrementId() . '_RiskInfo.xml';
            $dir = $this->directory->getPath('var');
            $path = $dir . DIRECTORY_SEPARATOR . $riskFile;
            $myfile = fopen($path, "w");
            fwrite($myfile, $data);
            fclose($myfile);
            chmod($path, 0777);
            return $path;
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());

            return null;
        }
    }

    /**
     * Check user email is confirmed or not.
     *
     * @param  int $customer_id Customer Id.
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
     *
     * @return string
     */
    public function getGuestCustomerFirstName(\Magento\Sales\Model\Order $order)
    {
        return $order->getBillingAddress()->getFirstName();
    }

    /**
     * Get last name.
     *
     * @return string
     */
    public function getGuestCustomerLastName(\Magento\Sales\Model\Order $order)
    {
        return $order->getBillingAddress()->getLastName();
    }
    
    /**
     * Get escaping special char.
     *
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
     *
     * @return string
     */
    public function getPaddedPhone($telephone = null)
    {
        $newTelephone = preg_replace("/\D/", "", $telephone);
        if (strlen($newTelephone) != 15 && strlen($newTelephone) < 15) {
            return str_pad($newTelephone, 15, '0', STR_PAD_LEFT);
        } else {
            return $newTelephone;
        }
    }
    
    /**
     * Get padded phone number.
     *
     * @return string
     */
    public function getRegionCode($country_id = null, $regionName = null)
    {
        $region = $this->regionDirectory->getCollection()
            ->addFieldToSelect('country_id', 'name')
            ->addFieldToSelect('code')
            ->addFieldToFilter('country_id', $country_id)
            ->addFieldToFilter('name', $regionName)->getFirstItem();
                        
        return $region->getCode();
    }
    
    /**
     * Get ISO-8601 timestamp.
     *
     * @return timestamp
     */
    public function getConvertedTime($time = null)
    {
        return date('Y-m-d\TH:i:s\Z', strtotime($time));
    }
}
