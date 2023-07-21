<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Product\Price;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
class Tier extends \Magento\Framework\View\Element\Template{
	
    protected $_product;
	
    /**
     * Websites cache
     *
     * @var array
     */
    protected $_websites;
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;
    /**
     * Catalog data
     *
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;
	
    /**
     * @var GroupManagementInterface
     */
    protected $_groupManagement;
	
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;	
	
	/**
     * Customer groups cache
     *
     * @var array
     */
    protected $_customerGroups;
	
    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;
	
	protected $_coreRegistry = null;
	
	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Module\Manager $moduleManager,
		\Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
		\Magento\Framework\Locale\CurrencyInterface $localeCurrency,
		\Magento\Directory\Helper\Data $directoryHelper,
		\Magento\Framework\Registry $coreRegistry,
		GroupRepositoryInterface $groupRepository,
		GroupManagementInterface $groupManagement,
        array $data = []			
	){
		parent::__construct($context, $data);
		$this->moduleManager = $moduleManager;
		$this->_groupManagement = $groupManagement;
		$this->_directoryHelper = $directoryHelper;		
		$this->_groupRepository = $groupRepository;		
		$this->_localeCurrency = $localeCurrency;
		$this->_searchCriteriaBuilder = $searchCriteriaBuilder;
		$this->_coreRegistry = $coreRegistry;
	}
	
	public function getProduct(){
		if(!$this->_product) {
			$this->_product = $this->_coreRegistry->registry('current_product');
		}
		return $this->_product;
	}
	protected function getAttributes(){
		if($this->_product){
			$product=$this->_product;
		}else{
			$product=$this->getProduct();
		}
		return $product->getAttributes();
	}
	
    /**
     * Gets the 'tear_price' array from the product
     *
     * @param Product $product
     * @param string $key
     * @param bool $returnRawData
     * @return array
     */
    protected function getExistingPrices($product, $key, $returnRawData = false)
    {
        $prices = $product->getData($key);

        if ($prices === null) {
            $attribute = $product->getResource()->getAttribute($key);
            if ($attribute) {
                $attribute->getBackend()->afterLoad($product);
                $prices = $product->getData($key);
            }
        }

        if ($prices === null || !is_array($prices)) {
            return ($returnRawData ? $prices : []);
        }

        return $prices;
    }
	
	public function getTierPriceCode(){
		return \Magento\Catalog\Pricing\Price\TierPrice::PRICE_CODE;
	}
    /**
     * Gets list of product tier prices
     *
     * @param Product $product
     * @return \Magento\Catalog\Api\Data\ProductTierPriceInterface[]
     */
    public function getTierPrices($product)
    {
        $prices = [];
        $tierPrices = $this->getExistingPrices($product, $this->getTierPriceCode());
        
        return $tierPrices;
    }	
	
	public function getTierValues()
    {
		if($this->_product){
			$product=$this->_product;
		}else{
			$product=$this->getProduct();
		}
        $data = $this->getTierPrices($product);

        $values = [];        

        if (is_array($data)) {
            $values = $this->_sortValues($data);
        }

        $currency = $this->_localeCurrency->getCurrency($this->_directoryHelper->getBaseCurrencyCode());
		$value['symbol'] = $currency->getSymbol();
        foreach ($values as &$value) {
            $value['readonly'] = $value['website_id'] == 0 &&
                $this->checkShowWebsiteColumn() &&
                !$this->checkAllowChangeWebsite();
            $value['price'] =
                $currency->toCurrency($value['price'], ['display' => \Magento\Framework\Currency::NO_SYMBOL]);
        }
		
        return $values;			
	}	
	
	public function checkScopeGlobal(){
		$_attributes=$this->getAttributes();
		$attribute=$_attributes[$this->getTierPriceCode()];
		return $attribute->isScopeGlobal();
	}
    /**
     * Retrieve allowed customer groups
     *
     * @param int|null $groupId  return name by customer group id
     * @return array|string
     */
    public function getTierCustomerGroups($groupId = null)
    {
        if ($this->_customerGroups === null) {
            if (!$this->moduleManager->isEnabled('Magento_Customer')) {
                return [];
            }
            $this->_customerGroups = [$this->_groupManagement->getAllCustomersGroup()->getId() => __('ALL GROUPS')];
            /** @var \Magento\Customer\Api\Data\GroupInterface[] $groups */
            $groups = $this->_groupRepository->getList($this->_searchCriteriaBuilder->create());
            foreach ($groups->getItems() as $group) {
                $this->_customerGroups[$group->getId()] = $group->getCode();
            }
        }

        if ($groupId !== null) {
            return isset($this->_customerGroups[$groupId]) ? $this->_customerGroups[$groupId] : [];
        }

        return $this->_customerGroups;
    }	
    /**
     * Show group prices grid website column
     *
     * @return bool
     */
    public function checkShowWebsiteColumn()
    {
        if ($this->checkScopeGlobal() || $this->_storeManager->isSingleStoreMode()) {
            return false;
        }
        return true;
    }
	
    /**
     * Check is allow change website value for combination
     *
     * @return bool
     */
    public function checkAllowChangeWebsite()
    {		
		if(!$this->_product->getId()) return true;
		
        if (!$this->checkShowWebsiteColumn() || $this->_product->getStoreId()) {
            return false;
        }
        return true;
    }	
	
	public function getTierApplyTo(){
        $attributes = [];
		$_attributes=$this->getAttributes();
        foreach ($_attributes as $key => $attribute) {
			if($key==$this->getTierPriceCode()){
				$attributes[] = $attribute->getApplyTo();
				break;
			}
        }		
		return $attributes;
	}
	
	/**
	 * @return void
	 */
	protected function processGetWebsite() {
	
        $this->_websites = [
            0 => ['name' => __('All Websites'), 'currency' => $this->_directoryHelper->getBaseCurrencyCode()]
        ];

        if (!$this->checkScopeGlobal() && $this->getProduct()->getStoreId()) {
            /** @var $website \Magento\Store\Model\Website */
            $website = $this->_storeManager->getStore($this->getProduct()->getStoreId())->getWebsite();

            $this->_websites[$website->getId()] = [
                'name' => $website->getName(),
                'currency' => $website->getBaseCurrencyCode()
            ];
        } elseif (!$this->checkScopeGlobal()) {
            $websites = $this->_storeManager->getWebsites();
            $productWebsiteIds = $this->getProduct()->getWebsiteIds();
            foreach ($websites as $website) {
                /** @var $website \Magento\Store\Model\Website */
                if (!in_array($website->getId(), $productWebsiteIds)) {
                    continue;
                }
                $this->_websites[$website->getId()] = [
                    'name' => $website->getName(),
                    'currency' => $website->getBaseCurrencyCode()
                ];
            }
        }
	}
	
    /**
     * Retrieve allowed for edit websites
     *
     * @return array
     */
    public function getTierWebsites()
    {
        if ($this->_websites !== null) {
            return $this->_websites;
        }
		$this->processGetWebsite();
        return $this->_websites;
    }	
    /**
     * Show website column and switcher for group price table
     *
     * @return bool
     */
    public function checkMultiWebsites()
    {
        return !$this->_storeManager->isSingleStoreMode();
    }

    /**
     * Sort values
     *
     * @param array $data
     * @return array
     */
    protected function _sortValues($data)
    {
        usort($data, [$this, '_sortTierPrices']);
        return $data;
    }

    /**
     * Sort tier price values callback method
     *
     * @param array $a
     * @param array $b
     * @return int
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _sortTierPrices($a, $b)
    {
        if ($a['website_id'] != $b['website_id']) {
            return $a['website_id'] < $b['website_id'] ? -1 : 1;
        }
        if ($a['cust_group'] != $b['cust_group']) {
            return $this->getTierCustomerGroups($a['cust_group']) < $this->getTierCustomerGroups($b['cust_group']) ? -1 : 1;
        }
        if ($a['price_qty'] != $b['price_qty']) {
            return $a['price_qty'] < $b['price_qty'] ? -1 : 1;
        }

        return 0;
    }
}