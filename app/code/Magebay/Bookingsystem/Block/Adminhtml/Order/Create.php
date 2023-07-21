<?php

namespace Magebay\Bookingsystem\Block\Adminhtml\Order;
 
use Magento\Backend\Block\Template;

class Create extends Template
{
	/**
     * Core registry
     *
     * @var \Magento\Framework\Registry
    */
    protected $_coreRegistry;
	/**
	* var Magebay\Bookingsystem\Model\Booking
	**/
	protected $_bookingsFactory;
	/**
     * Helper Date
     *
     * @var \Magebay\Bookingsystem\Helper\BkHelperDate
    */
	protected $_bkHelperDate;
	/**
     * optionsFactory Model
     *
     * @var \Magebay\Bookingsystem\Model\OptionsFactory
    */
	protected $_optionsFactory;
	/**
     *
     * @var Magento\Framework\Stdlib\DateTime\Timezone 
    */
	protected $_timezone;
	/** Helper BkOrderHelper
     *
     * @var \Magebay\Bookingsystem\Helper\BkSimplePriceHelper
    **/
	protected $_bkSimplePriceHelper;
	/**
     *
     * @var Magento\Framework\Pricing\Helper\Data 
    */
	protected $_priceHelper;
	/**
     *
     * @var Magento\Directory\Model\Currency
    */
	protected $_currency;
	/**
     *
     * @var Magento\Review\Model\Review\SummaryFactory
    */
	protected $_summaryFactory;
	/**
     *
     * @var\Magento\Backend\Model\Auth\Session
    */
	protected $_bkSession;
	/** Helper BkOrderHelper
     *
     * @var \Magebay\Bookingsystem\Helper\BkText
    **/
	protected $_bkText;
	/** Helper BkOrderHelper
     *
     * @var \Magebay\Bookingsystem\Model\OptionsdropdownFactory
    **/
	protected $_optionsdropdownFactory;
	/**
     *
     * @var \\Magento\Quote\Model\Quote\Item
    **/
	protected $_quoteItem;
	/** Helper BkOrderHelper
     *
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\OptionFactory
    **/
	protected $_itemOptionFactory;
	/**
     * Helper RentPrice
     *
     * @var \Magebay\Bookingsystem\Helper\RentPrice
    */
	protected $_rentPrice;
	/**
     * @var \Magento\Backend\Model\Session\Quote
    */
	protected $_quoteSession;
	/**
     * @var \Magebay\Bookingsystem\Model\DiscountsFactory
    */
	protected $_discountsFactory;
	/**
     * @var Magebay\Bookingsystem\Model\FacilitiesFactory
    */
	protected $_facilitiesFactory;
	/**
     *
     * @var  Magebay\Bookingsystem\Model\Image
    **/
	protected $_imageModel;
	function __construct(
		\Magento\Framework\Registry $coreRegistry,
		\Magento\Backend\Block\Widget\Context $context,
		\Magebay\Bookingsystem\Model\BookingsFactory $BookingsFactory,
		\Magebay\Bookingsystem\Helper\BkHelperDate $bkHelperDate,
		\Magebay\Bookingsystem\Model\OptionsFactory $optionsFactory,
		\Magento\Framework\Stdlib\DateTime\Timezone $timezone,
		\Magebay\Bookingsystem\Helper\BkSimplePriceHelper $bkSimplePriceHelper,
		\Magento\Framework\Pricing\Helper\Data $priceHelper,
		\Magento\Directory\Model\Currency $currency,
		\Magento\Review\Model\Review\SummaryFactory $summaryFactory,
		\Magento\Backend\Model\Auth\Session $bkSession,
		\Magebay\Bookingsystem\Helper\BkText $bkText,
		\Magebay\Bookingsystem\Model\OptionsdropdownFactory $optionsdropdownFactory,
		\Magento\Quote\Model\Quote\Item $quoteItem,
		\Magento\Quote\Model\Quote\Item\OptionFactory $itemOptionFactory,
		\Magebay\Bookingsystem\Helper\RentPrice $rentPrice,
		\Magento\Backend\Model\Session\Quote $quoteSession,
		\Magebay\Bookingsystem\Model\DiscountsFactory $discountsFactory,
		\Magebay\Bookingsystem\Model\FacilitiesFactory $facilitiesFactory,
		\Magebay\Bookingsystem\Model\Image $imageModel,
		array $data = []
	)
	{
		$this->_coreRegistry = $coreRegistry;
		$this->_bkHelperDate = $bkHelperDate;
		$this->_bookingsFactory = $BookingsFactory;
		$this->_optionsFactory = $optionsFactory;
		$this->_timezone = $timezone;
		$this->_bkSimplePriceHelper = $bkSimplePriceHelper;
		$this->_priceHelper = $priceHelper;
		$this->_currency = $currency;
		$this->_summaryFactory = $summaryFactory;
		$this->_bkSession = $bkSession;
		$this->_bkText = $bkText;
		$this->_optionsdropdownFactory = $optionsdropdownFactory;
		$this->_quoteItem = $quoteItem;
		$this->_itemOptionFactory = $itemOptionFactory;
		$this->_rentPrice = $rentPrice;
		$this->_quoteSession = $quoteSession;
		$this->_discountsFactory = $discountsFactory;
		$this->_facilitiesFactory = $facilitiesFactory;
		$this->_imageModel = $imageModel;
		parent::__construct($context, $data);
	}
	/**
	* set registry booking data
	* @param int productId
	* @return $bookingItem
	**/
	function getBooking($bookingId = 0)
	{
		$booking = null;
		/* if(!$this->_coreRegistry->registry('bk_booking_data_order'))
		{
			$bookingModel = $this->_bookingsFactory->create();
			$bookingId = $this->getBookingId();
			echo $bookingId ;
			if($bookingId > 0)
			{
				$booking = $bookingModel->getBooking($bookingId);
				$this->_coreRegistry->register('bk_booking_data_order',$booking);
			}
		}
		else
		{
			$booking = $this->_coreRegistry->registry('bk_booking_data_order');
		} */
		$bookingId = $bookingId > 0 ? $bookingId : $this->getBookingId();
		/* $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$quoteModel = $objectManager->get('Magento\Quote\Model\Quote\Item'); */
		$quoteModel = $this->_quoteItem;
		if($this->getRequest()->getActionName() == 'configureQuoteItems')
		{
			$dataItem = $quoteModel->load($bookingId);
			$bookingId = $dataItem->getProductId();
		}
		$bookingModel = $this->_bookingsFactory->create();
		if($bookingId > 0)
		{
			$booking = $bookingModel->getBooking($bookingId);
		}
		return $booking;
	}
	function getBkBookingItem()
	{
		return $this->getBookingData();
	}
	/** 
	* Get facilities
	* @param $bookingId, string $bookingType
	* @return $items
	**/
	function getBkFacilities($bookingId,$bookingType)
	{
		$fatilityModel = $this->_facilitiesFactory->create();
		$arSelect = array('*');
		$arConditoin = array('facility_booking_type'=>$bookingType,'facility_status'=>1);
		$collection = $fatilityModel->getBkFacilitiesById($bookingId,$arSelect,$arConditoin);
		return $collection;
	}
	/**
	* get Core Bk Helper Date
	**/
	function getBkHelperDate()
	{
		return $this->_bkHelperDate;
	}
	/**
	* get addons Selles 
	* @return array $itens
	**/
	function getAddonsSelles()
	{
		$booking = $this->getBookingData();
		$bookingId = $booking->getId();
		$model = $this->_optionsFactory->create();
		$collection = $model->getBkOptions($bookingId);
		return $collection;
	}
	/**
	* get Values options
	* @return $items
	**/
	function getBkOptionSelectValues($optionId)
	{
		$model = $this->_optionsdropdownFactory->create();
		$collection = $model->getBkValueOptions($optionId);
		return $collection;
	}
	/** 
	* get infor request by $quoteItem
	**/
	function getBkRequestOption()
	{
		$itemoption = null;
		$arRequestOptions = array();
		if($this->getRequest()->getActionName() == 'configureQuoteItems')
		{
			$itemId = $this->getRequest()->getParam('id',0);
			$itemOptionModel = $this->_itemOptionFactory->create();
			$collection = $itemOptionModel->getCollection()
				->addFieldToFilter('item_id',$itemId)
				->addFieldToFilter('code','info_buyRequest');
			
			if(count($collection))
			{
				$itemoption = $collection->getFirstItem();
				$arRequestOptions = unserialize($itemoption->getValue());
			}
		}
		return $arRequestOptions;
	}
	function getBkQuoteItemId()
	{
		$itemId = 0;
		if($this->getRequest()->getActionName() == 'configureQuoteItems')
		{
			$itemId = $this->getRequest()->getParam('id',0);
		}
		return $itemId;
	}
	function getBkHelperText()
	{
		return $this->_bkText;
	}
	/** get Current Time from core
	* return int $time
	**/
	function getBkTmpTime()
	{
		return $this->_timezone->scopeTimeStamp();
	}
	/*
	* get price when product load
	*/
	function getBkCurrentPrice()
	{
		$itemId = 0;
		$action = $this->getRequest()->getActionName();
		if($action == 'configure')
		{
			$itemId = $this->getRequest()->getParam('id',0);
		}
		$timeCurrent = $this->getBkTmpTime();
		$checkIn = date('Y-m-d',$timeCurrent);
		$booking = $this->getBkBookingItem();
		$arPrices = $this->_bkSimplePriceHelper->getPriceBetweenDays($booking,$checkIn,$checkIn,1,$itemId);
		$useDefaultPrice = $this->getBkHelperDate()->getFieldSetting('bookingsystem/setting/default_price');
		$price = 0;
		if($arPrices['str_error'] == '')
		{
			$price = $arPrices['total_promo'] > 0 ? $arPrices['total_promo'] : $arPrices['total_price'];
		}
		else
		{
			$arPrices = $this->getBkRentPriceHelper()->getPriceOfDay($booking->getId(),$checkIn,$booking->getBookingType());
			$price = $arPrices['promo'] > 0 ? $arPrices['promo'] : $arPrices['price'];
		}
			
		if($useDefaultPrice == 1)
		{
			if($booking->getSpecialPrice() > 0)
			{
				$price += $booking->getSpecialPrice();
			}
			else
			{
				$price += $booking->getPrice();
			}
		}
		return $price;
	}
	/* get bk Discount */
	function getBkDiscounts()
	{
		$formatDate = $this->getBkHelperDate()->getFieldSetting('bookingsystem/setting/format_date');
		$booking = $this->getBkBookingItem();
		$model = $this->_discountsFactory->create();
		$intToday = $this->_timezone->scopeTimeStamp();
		$symbol = $this->_currency->getCurrencySymbol();
		$collection = $model->getBkDiscountItems($booking->getId(),$formatDate,$intToday,$symbol);
		return $collection;
	}
	/* get seesion in back end */
	function getBkSession()
	{
		return $this->_bkSession;
	}
	/* get quote session */
	function getBkQuoteSession()
	{
		return $this->_quoteSession;
	}
	/*
	* get review product
	*/
	function getBkReview($productId)
	{
		$reviewModel = $this->_summaryFactory->create();
		$currentStore = $this->getbkCurrentStore();
		$summary = $reviewModel->setStoreId($currentStore)->load($productId);
		return $summary;
	}
	function getBkCurrencySymboy()
	{
		return $this->_currency->getCurrencySymbol();
	}
	/**
	* get Core Helper Price
	**/
	function getBkPriceHelper()
	{
		return $this->_priceHelper;
	}
	function getBkRentPriceHelper()
	{
		return $this->_rentPrice;
	}
	function getBkBaseUrl()
	{
		return $this->_imageModel->getBaseUrl();
	}
	/**resize image**/
	function imageResize($image,$width,$height)
	{
		$urlImage = $this->_imageModel->imageResize($image,$width,$height);
		return $urlImage;
	}
}