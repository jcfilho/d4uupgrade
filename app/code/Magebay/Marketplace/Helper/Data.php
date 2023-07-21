<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class Data extends AbstractHelper
{
    //All path system config from system.xml
    const XML_PATH_ENABLED      = 'marketplace/general/enable';
    const XML_PATH_ADMIN_EMAIL     = 'marketplace/general/admin_email';
    const XML_PATH_COMMISSION   = 'marketplace/general/percent';
    const XML_PRODUCT_TYPE   = 'marketplace/general/product_type';
    const XML_PATH_ATTRIBUTE_SET_ID   = 'marketplace/general/magebay_marketplace_block_position';
    const XML_PATH_SELLER_APPROVAL   = 'marketplace/general/seller_approval';
    const XML_PATH_PRODUCT_APPROVAL   = 'marketplace/general/product_approval';  
    const XML_PATH_PRODUCT_EDIT_APPROVAL   = 'marketplace/general/product_edit_approval';  
    const XML_PATH_REVIEW_APPROVAL   = 'marketplace/general/review_approval';  
    const XML_PATH_REVIEW_APPROVAL_ORDER   = 'marketplace/general/review_approval_order';      
    const XML_PATH_SELLER_POLICY_APPROVAL   = 'marketplace/general/seller_policy_approval';  
    const XML_PATH_CATEGORYIDS   = 'marketplace/general/categoryids';  
    const XML_PATH_TAXMANAGE   = 'marketplace/general/taxmanage'; 
    const XML_PATH_MULTIVENDORLABEL   = 'marketplace/general/multivendorlabel'; 
    const XML_PATH_MULTIVENDORBUTTON   = 'marketplace/general/multivendorbutton'; 
    const XML_PATH_MULTIVENDORDESCRIPTION   = 'marketplace/general/multivendordescription'; 
    const XML_PATH_SPECIFICCOUNTRY   = 'marketplace/general/specificcountry'; 
    const XML_PATH_ALLOWSPECIFIC   = 'marketplace/general/allowspecific'; 
    const XML_PATH_TRANS_KEY   = 'marketplace/general/trans_key'; 
    const XML_PATH_EMAIL_CONTACT_VENDOR   = 'marketplace/general/email_contact_vendor'; 
    const XML_PATH_COMMISSION_12   = 'marketplace/goldlife/commission_12'; 
    const XML_PATH_SELLER_MEMBERSHIP_ENABLED          = 'sellermembership/general/enable';
    
	protected $_objectmanager;
	protected $assetRepo;
	protected $categoryRepository;
	protected $_storeManager;
	protected $_categoryFactory;
	protected $_category;
	protected $_setFactory;
    protected $_localeCurrency;
	
    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
		ObjectManagerInterface $objectmanager,
		\Magento\Framework\View\Asset\Repository $assetRepo,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		CategoryRepositoryInterface $categoryRepository,
		\Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency
    ) 
	{
        parent::__construct($context);
		$this->assetRepo = $assetRepo;
		$this->_storeManager = $storeManager;
		$this->categoryRepository = $categoryRepository;
		$this->_objectmanager=$objectmanager;
		$this->_setFactory=$setFactory;
		$this->_categoryFactory = $categoryFactory;
        $this->_localeCurrency = $localeCurrency;
    }
 
   /**
     * Check for module is enabled in frontend
     *
     * @return bool
     */
    public function isEnabledInFrontend($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get Admin Email
     *
     * @return bool
     */
    public function getAdminEmail($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADMIN_EMAIL,
            ScopeInterface::SCOPE_STORE
        );
    }
 
   /**
     * Get Default Commission In Percentage
     *
     * @return string
     */
    public function getCommission()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COMMISSION,
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Get Product Type Enable For Add Product
     *
     * @return string
     */
    public function getProductType()
    {
        return $this->scopeConfig->getValue(
            self::XML_PRODUCT_TYPE,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get Attribute Set ID 
     *
     * @return string
     */
    public function getAttributeSetID()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ATTRIBUTE_SET_ID,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get Seller Approval Required
     *
     * @return bool
     */
    public function getSellerApprovalRequired()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SELLER_APPROVAL,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get Product Approval Required
     *
     * @return bool
     */
    public function getProductApprovalRequired()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_APPROVAL,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get Product Update Approval Required
     *
     * @return bool
     */
    public function getProductUpdateApprovalRequired()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_EDIT_APPROVAL,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get Review Approval Required
     *
     * @return bool
     */
    public function getReviewApprovalRequired()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REVIEW_APPROVAL,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get Review on only Order Purchase
     *
     * @return bool
     */
    public function getReviewOnOnlyOrderPurchase()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REVIEW_APPROVAL_ORDER,
            ScopeInterface::SCOPE_STORE
        );
    }
	
    /**
     * Get Seller Policies Enable At Frontend
     *
     * @return bool
     */
    public function getSellerPolicies()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SELLER_POLICY_APPROVAL,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get Allowed Categories For Seller To Add Products
     *
     * @return string
     */
    public function getAllowedCategoriesForSeller()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CATEGORYIDS,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get Move Product Tax To Seller Account
     *
     * @return bool
     */
    public function getMoveProductTax()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TAXMANAGE,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get Multi-Vendor Landing Page Labe
     *
     * @return string
     */
    public function getPageLabe()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MULTIVENDORLABEL,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get Multi-Vendor Landing Page Button Label
     *
     * @return string
     */
    public function getPageButtonLabel()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MULTIVENDORBUTTON,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get About Multi-Vendor
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MULTIVENDORDESCRIPTION,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Retrieve currency Symbol.
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->_localeCurrency->getCurrency(
            $this->_storeManager->getStore()->getBaseCurrencyCode()
        )->getSymbol();
    }
    
    /**
     * Get Payment From Specific Countries
     *
     * @return string
     */
    public function getPaymentFromSpecificCountries()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SPECIFICCOUNTRY,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get Payment From Applicable Countries
     *
     * @return string
     */
    public function getPaymentFromApplicableCountries()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ALLOWSPECIFIC,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get Transaction Key
     *
     * @return string
     */
    public function getTransactionKey()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TRANS_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get Email Template For Contact Vendor
     *
     * @return string
     */
    public function getEmailTemplateContactVendor()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_CONTACT_VENDOR,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get Seller Commission For First 12 Months 
     *
     * @return string
     */
    public function getCommission12()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COMMISSION_12,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get Seller Member Ship Enable 
     *
     * @return string
     */
    public function getSellerMembershipIsEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SELLER_MEMBERSHIP_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }
    
	public function checkIsSeller(){			
		$flag=false;
		$customerSession=$this->_objectmanager->create('Magento\Customer\Model\Session');
		$_dataObject=$customerSession->getCustomerData();
		if(is_object($_dataObject)){
    		$_customerId=$_dataObject->getId();
    		$_customerCollection=$this->getSellerById($_customerId);
    		if(!count($_customerCollection)) return false;
    		if($_customerCollection[0]['is_vendor'] && $_customerCollection[0]['userstatus'])
			$flag=true;
		}
		return $flag;
	}
	
	public function getSellerById($id){		
		$sellerCollection=$this->_objectmanager->create('Magebay\Marketplace\Model\ResourceModel\Sellers\Collection')->addFieldToFilter('user_id',$id);
		return $sellerCollection->getData();
	}

	public function checkStoreUrl($storeUrl){
		$sellerCollection=$this->_objectmanager->create('Magebay\Marketplace\Model\ResourceModel\Sellers\Collection')->addFieldToFilter('storeurl',array('like'=>$storeUrl));
		return $sellerCollection->getData();
	}
	
	public function getTaxConnection(){
		$expectedResult = [];
		$collection=$this->_objectmanager->create('Magento\Tax\Model\ResourceModel\TaxClass\Collection');
        foreach ($collection as $taxClass) {
            if ($taxClass->getClassType() == \Magento\Tax\Api\TaxClassManagementInterface::TYPE_PRODUCT) {
                $expectedResult[] = ['value' => $taxClass->getId(), 'label' => $taxClass->getClassName()];
            }
        }		
		return $expectedResult;
	}

	public function getSpacerImage(){
		return $this->assetRepo->getUrl('images/spacer.gif');
	}
	
	public function getCategoryById($categoryId){			
		$category = $this->categoryRepository->get($categoryId, $this->_storeManager->getStore()->getId());			
		return $category;									
	}
	
	/**
     * Get category object
     *
     * @return \Magento\Catalog\Model\Category
     */
	public function getCategory($categoryId) 
	{
		$this->_category = $this->_categoryFactory->create();
		$this->_category->load($categoryId);		
		return $this->_category;
	}
	
	public function getOptionSetGroup(){
		$setOptionArray = $this->_setFactory->create()->getResourceCollection()
		->addFieldToFilter('entity_type_id',4)
		->load()->toOptionArray();
        return $setOptionArray;
	}	
    
    public function checkPurchasedProduct($product_id){
        $flag = false;
        $customerSession = $this->_objectmanager->create('Magento\Customer\Model\Session');
		$_dataObject = $customerSession->getCustomerData();
        if(is_object($_dataObject)){
            $coreOrderModel = $this->_objectmanager->create('Magento\Sales\Model\Order')
                                                    ->getCollection()
                                                    ->addAttributeToFilter('customer_id',$_dataObject->getId());
            if(count($coreOrderModel) > 0){
                foreach($coreOrderModel as $order){
                    $orderItems = $order->getAllItems();
                    foreach($orderItems as $od){
                        if($product_id == $od->getProductId()){
                            $flag = true;
                            break;
                        }
                    }
                }
            }
        }
        return $flag;
    }
    
    public function checkLogin(){
        $flag = false;
        $customerSession = $this->_objectmanager->create('Magento\Customer\Model\Session');
		if($customerSession->isLoggedIn()) {
           $flag = true;
        }
        return $flag;
    }  
    
    public function getMagentoVersion(){
        $productMetadata = $this->_objectmanager->get('Magento\Framework\App\ProductMetadataInterface');
        $version = $productMetadata->getVersion();
        return $version;
    }      
	
	/** Get store config data in system**/
    public function getStoreConfigData($path) {
         $config = $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
         return $config;
    }
	
	public function get_content_id($file,$id){
		$h1tags = preg_match_all("/(<div id=\"{$id}\">)(.*?)(<\/div>)/ismU",$file,$patterns);
		$res = array();
		array_push($res,$patterns[2]);
		array_push($res,count($patterns[2]));
		return $res;
	}
	public function get_div($file,$id){
	    $h1tags = preg_match_all("/(<div.*>)(\w.*)(<\/div>)/ismU",$file,$patterns);
	    $res = array();
	    array_push($res,$patterns[2]);
	    array_push($res,count($patterns[2]));
	    return $res;
	}
    public function get_domain($url)   {   
		//$dev = 'dev';
		$dev = $_SERVER['HOSTNAME'];
		if ( !preg_match("/^http/", $url) )
			$url = 'http://' . $url;
		if ( $url[strlen($url)-1] != '/' )
			$url .= '/';
		$pieces = parse_url($url);
		$domain = isset($pieces['host']) ? $pieces['host'] : ''; 
		if ( preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs) ) { 
			$res = preg_replace('/^www\./', '', $regs['domain'] );
			return $res;
		}   
		return $dev;
	}
}