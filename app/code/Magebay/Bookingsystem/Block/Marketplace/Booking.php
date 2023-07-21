<?php

namespace Magebay\Bookingsystem\Block\Marketplace;
 
use Magento\Backend\Block\Template;
use Magento\Backend\Model\Auth\Session as BackendSession;
use Magebay\Bookingsystem\Model\Options as BkOptions;
use Magebay\Bookingsystem\Model\Optionsdropdown;
use Magebay\Bookingsystem\Helper\BkText;
use Magebay\Bookingsystem\Model\DiscountsFactory;
use Magebay\Bookingsystem\Model\FacilitiesFactory;

class Booking extends Template
{
	/**
     * @param Magebay\Bookingsystem\Model\Options
     * 
    */
	protected $_bkOptions;
	/**
     * @param Magebay\Bookingsystem\Model\Optionsdropdown
     * 
    */
	protected $_optionsdropdown;
	/**
     * @param \Magebay\Bookingsystem\Helper\BkText;
     * 
    */
	protected $_bkText;
	/**
     * @param \Magento\Backend\Model\Auth\Session
     * 
    */
	protected $_backendSession;
	/**
     * 
     * @var Magebay\Bookingsystem\Model\DiscountsFactory;
     */
	 protected $_discountsFactory;
	 /**
     * 
     * @var Magebay\Bookingsystem\Model\FacilitiesFactory;
     */
	 protected $_facilitiesFactory;
	function __construct(
		\Magento\Backend\Block\Widget\Context $context,
		BackendSession $backendSession,
		BkOptions $bkOptions,
		Optionsdropdown $optionsdropdown,
		BkText $bkText,
		DiscountsFactory $discountsFactory,
		FacilitiesFactory $facilitiesFactory,
		array $data = []
	)
	{
		parent::__construct($context, $data);
		$this->_backendSession = $backendSession;
		$this->_bkOptions = $bkOptions;
		$this->_optionsdropdown = $optionsdropdown;
		$this->_bkText = $bkText;
		$this->_discountsFactory = $discountsFactory;
		$this->_facilitiesFactory = $facilitiesFactory;
	}

	/* 
	* get max options Id
	*/
	function getMaxOptionId()
	{
		$adminSession = $this->_backendSession;
		return $adminSession->getMaxOptionId();
	}
	function getBkRequest()
	{
		return $this->_request;
	}
	/**
	* get sell options
	* @return array $data
	**/
	function getBkOptions()
	{
		$bookingId = $this->getBookingId();
		$bookingType = $this->getBookingType();
		$options = $this->_bkOptions;
		$collection = $options->getBkOptions($bookingId,$bookingType);
		return $collection;
	}
	/**
	* get sell options
	* @return array $data
	**/
	function getBkValueOptions($optionId)
	{
		$collection = $this->_optionsdropdown->getBkValueOptions($optionId);
		return $collection;
	}
	function getBkArText($text,$textTrans,$store_id)
	{
		$arText = $this->_bkText->getBkArTextByStore($text,$textTrans,$store_id);
		return $arText;
	}
	/**
	* get text transalte
	* 
	**/
	function getBkStrText($text,$textTrans,$store_id)
	{
		return $this->_bkText->showTranslateText($text,$textTrans,$store_id);
	}
	function shortDescription($text,$number)
	{
		return $this->_bkText->cutDescription($text,$number);
	}
	/* function for discount */
	function getMaxDiscountId()
	{
		$adminSession = $this->_backendSession;
		return $adminSession->getMaxDiscountId();
	}
	function getBkDiscounts()
	{
		$bookingId = $this->getBookingId();
		$bookingType = $this->getBookingType();
		$discountModel = $this->_discountsFactory->create();
		$collection = $discountModel->getBkDiscounts($bookingId,$bookingType);
		return $collection;
	}
	/**
	* get Facilities
	* @return array $data
	**/
	function getBkFacilities()
	{
		$bookingType = $this->getBookingType();
		$arSelect = array('facility_id','facility_title','facility_description','facility_title_transalte','facility_des_translate');
		$conditions = array('facility_booking_type'=>$bookingType,'facility_status'=>1);
		$model = $this->_facilitiesFactory->create();;
		$collection = $model->getBkFacilities($arSelect,$conditions);
		return $collection;
	}
	/**
	* get Facilities by product Id
	* @return array $data
	**/
	function getBkFacilitiesById()
	{
		$bookingId = $this->getBookingId();
		$bookingType = $this->getBookingType();
		$arSelect = array('facility_id',);
		$conditions = array('facility_booking_type'=>$bookingType);
		$model = $this->_facilitiesFactory->create();;
		$collection = $model->getBkFacilitiesById($bookingId,$arSelect,$conditions);
		$facilityIds = array();
		foreach($collection as $collect)
		{
			$facilityIds[$collect->getId()] = $collect->getId();
		}
		return $facilityIds;
	}
	function getBkCurrentStoreId()
	{
		return $this->_bkText->getbkCurrentStore();
	}
}