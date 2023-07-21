<?php
 
namespace Magebay\Bookingsystem\Model;
 
use Magento\Framework\Model\AbstractModel;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\ResourceConnection; 
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\Db;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Catalog\Model\Product as ProductModel;
class Bookings extends AbstractModel
{
	/**
     *
     * @var Magento\Framework\App\ResourceConnection
    */
	protected $_resourceConnection;
	protected $_productModel;
	protected $_countryCollection;
	protected $_storeRepository;
    /**

     * @param IsActive $statusList
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
		ProductModel $productModel,
		ResourceConnection $resourceConection,
		\Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection,
        \Magento\Store\Model\StoreRepository $storeRepository,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
		$this->_productModel = $productModel;
		$this->_resourceConnection = $resourceConection;
		$this->_countryCollection = $countryCollection;
		$this->_storeRepository = $storeRepository;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
		
    }
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Magebay\Bookingsystem\Model\ResourceModel\Bookings');
    }
	/**
	* get all bookings
	* return array $bookings
	**/
	function getBookings($arrayAttributeSelect = array('*'),$arAttributeConditions = array(),$condition = '',$arrayBooking = array('*'),$attributeSort = array(),$bookingSort = '',$limit = 0,$curPage = 1,$storeId = 0)
	{
		$resourceConnection = $this->_resourceConnection;
		$tableAlias = $resourceConnection->getTableName('booking_systems');
		$modelProduct = $this->_productModel;
		$collection = $modelProduct->getCollection()
				->addAttributeToSelect($arrayAttributeSelect);
		if(count($arAttributeConditions))
		{
			foreach($arAttributeConditions as $keyAtt => $attCondition)
			{
				$collection->addAttributeToFilter($keyAtt,$attCondition);
			}
		}
		$collection->getSelect()->join($tableAlias,'e.entity_id = '.$tableAlias.'.booking_product_id',$arrayBooking);
		if(count($attributeSort))
		{
			$collection->addAttributeToSort($attributeSort['filed'],$attributeSort['sort']);	
		}
		$collection->getSelect()->where($tableAlias.'.booking_product_id IS NOT NULL');
		$collection->getSelect()->where($tableAlias.'.store_id=?',$storeId);
		if($condition != '')
		{
			$collection->getSelect()->where($condition);
			
		}
		if($bookingSort != '')
		{
			$collection->getSelect()->order($tableAlias.'.'.$bookingSort);
		}
		if($limit > 0)
		{
			$collection->setPageSize($limit);
		}
		$collection->setCurPage($curPage);
		return $collection;
	}
	/* get booking item 
	* @return $booking
	*/
	function getBooking($productId,$arrayAttributeSelect = array('*'),$arAttributeConditions = array(),$condition = '',$arrayBooking = array('*'),$attributeSort = array(),$bookingSort = '',$limit = 0,$curPage = 1,$storeId = 0)
	{
		$arAttributeConditions = array('entity_id'=>$productId);
		$collection = $this->getBookings($arrayAttributeSelect,$arAttributeConditions,$condition,$arrayBooking,$attributeSort,$bookingSort,$limit,$curPage,$storeId);
		$collection = $collection->getFirstItem();
		return $collection;
	}
	function saveBooking($bookingParams,$productId)
	{
        $storeId = isset($bookingParams['store_id']) ? $bookingParams['store_id'] : 0;
        if($storeId == 0)
        {
            $serviceStart = '';
            $serviceEnd = '';
            if($bookingParams['booking_time'] == '2' || $bookingParams['booking_time'] == '3')
            {
                $arServiceStart = $bookingParams['booking_service_start'];
                $serviceStart = $arServiceStart['hour'].','.$arServiceStart['minute'].','.$arServiceStart['type'];
                $arServiceEnd = $bookingParams['booking_service_end'];
                $serviceEnd = $arServiceEnd['hour'].','.$arServiceEnd['minute'].','.$arServiceEnd['type'];
            }
            $bookingParams['booking_service_start'] = $serviceStart;
            $bookingParams['booking_service_end'] = $serviceEnd;
            $bookingParams['booking_id'] = $bookingParams['booking_temp_id'];
            $bookingParams['booking_product_id'] = $productId;
            if(isset($bookingParams['booking_state_id']) && (int)$bookingParams['booking_state_id'] > 0)
            {
                $bookingParams['booking_state'] = '';
            }
            if($bookingParams['booking_id'] == 0)
            {
                unset($bookingParams['booking_id']);
            }
            //add disable days
            $strDisableDay = '';
            $arDisableDay = isset($bookingParams['disable_days']) ? $bookingParams['disable_days'] : array();
            if(count($arDisableDay))
            {
                $strDisableDay = implode(',',$arDisableDay);
                $bookingParams['disable_days'] = $strDisableDay;
            }
            else
            {
                $bookingParams['disable_days'] = '';
            }
            $bookingParams['booking_type_intevals'] = isset($bookingParams['booking_type_intevals']) ? $bookingParams['booking_type_intevals'] : 0;
            //print_r($bookingParams);
            $this->setData($bookingParams)->save();
        }
	}
	/*
	* get bkAddressIds
	* @param array $bkAddress
	* return array $bookingIds
	**/
	function getBkAddressIds($address)
	{
		$strAddress = $address['address'];
		$city = trim($address['city']);
		$states = trim($address['states']);
		$strCountry = trim($address['country']);
		$collection = $this->getCollection();
		$arrayFiled = array();
		$arrayValue = array();
		if($strAddress != '')
		{
			$arrayFiled[] = 'auto_address';
			$arrayValue[] = array('like'=>'%'.$strAddress.'%');
		}
		if($city != '')
		{
			$arrayFiled[] =  'booking_city';
			$arrayValue[] = array('like'=>'%'.$city.'%');
		}
		if($states != '')
		{
			$arrayFiled[] =  'booking_state';
			$arrayValue[] = array('like'=>'%'.$states.'%');
		}
		if($city == '' && $states == '' && $strCountry != '')
		{
			$countries = $this->_countryCollection->loadByStore();
			foreach($countries as $country)
			{
				if($country->getName() == $strCountry)
				{
					$arrayFiled[] =  'booking_country';
					$arrayValue[] = array('like'=>'%'.$country->getId().'%');
					break;
				}
			}
		}
		$bookingIds = array();
		if(count($arrayFiled))
		{
			$collection = $this->getCollection();
			$collection->addFieldToFilter($arrayFiled,$arrayValue);
			foreach($collection as $collect)
			{
				$bookingIds[] = $collect->getBookingProductId();
			}
		}
		return $bookingIds;
	}
	/**
	* delete booking system
	* param $bookingId
	* return $this
	**/
	function deleteBkBooking($bookingId)
	{
		$collection = $this->getCollection()
			->addFieldToFilter('booking_product_id',$bookingId);
		$booking = $collection->getFirstItem();
		if($booking)
		{
			$bkBookingId = $booking->getId();
			$this->setId($bkBookingId)->delete();
		}
	}
    function saveBookingAddressStore($bookingParams,$storeId = 0)
    {
        //get default booking
        $dataSave = array();
        if($storeId == 0)
        {
            $allStores = $this->_storeRepository->getList();
            foreach ($allStores as $store) {
                $dataSave = array();
                $defaultCollection = $this->getCollection()
                    ->addFieldToFilter('booking_product_id',$bookingParams['booking_product_id'])
                    ->addFieldToFilter('store_id',0);
                $okDefault = false;
                if(count($defaultCollection))
                {
                    $defaultFirstItem = $defaultCollection->getFirstItem();
                    if($defaultFirstItem && $defaultFirstItem->getId())
                    {

                        $dataSave = $defaultFirstItem->getData();
                        $okDefault = true;
                        unset($dataSave['booking_id']);
                    }
                }
                $mStoreId = $store["store_id"];
                $collection = $this->getCollection()
                    ->addFieldToFilter('booking_product_id', $bookingParams['booking_product_id'])
                    ->addFieldToFilter('store_id',$mStoreId);
                if (count($collection)) {
                    $firstItem = $collection->getFirstItem();
                    if ($firstItem && $firstItem->getId()) {
                        if($okDefault)
                        {
                            //only update main data
                            $dataSave['booking_id'] = $firstItem->getId();
                            unset($dataSave['booking_phone']);
                            unset($dataSave['booking_email']);
                            unset($dataSave['booking_address']);
                            unset($dataSave['booking_city']);
                            unset($dataSave['booking_zipcode']);
                            unset($dataSave['booking_state']);
                            unset($dataSave['booking_country']);
                            unset($dataSave['auto_address']);
                        }
                    }
                }
                $dataSave['store_id'] = $mStoreId;
                if(count($dataSave))
                {
                    $this->setData($dataSave)->save();
                }
            }
        }
        else
        {
            $defaultCollection = $this->getCollection()
                ->addFieldToFilter('booking_product_id',$bookingParams['booking_product_id'])
                ->addFieldToFilter('store_id',0);
            if(count($defaultCollection))
            {
                $defaultFirstItem = $defaultCollection->getFirstItem();
                if($defaultFirstItem && $defaultFirstItem->getId())
                {
                    $dataSave = $defaultFirstItem->getData();
                    unset($dataSave['booking_id']);
                }
            }
            $collection = $this->getCollection()
                ->addFieldToFilter('booking_product_id',$bookingParams['booking_product_id'])
                ->addFieldToFilter('store_id',$storeId);
            if(count($collection))
            {
                $firstItem = $collection->getFirstItem();
                if($firstItem && $firstItem->getId())
                {
                    $dataSave['booking_id'] = $firstItem->getId();
                }
            }
            $dataSave['booking_phone'] = $bookingParams['booking_phone'];
            $dataSave['booking_email'] = $bookingParams['booking_email'];
            $dataSave['booking_address'] = $bookingParams['booking_address'];
            $dataSave['booking_city'] = $bookingParams['booking_city'];
            $dataSave['booking_zipcode'] = $bookingParams['booking_zipcode'];
            $dataSave['booking_state'] = $bookingParams['booking_state'];
            $dataSave['booking_country'] = $bookingParams['booking_country'];
            $dataSave['auto_address'] = $bookingParams['auto_address'];
            $dataSave['booking_product_id'] = $bookingParams['booking_product_id'];
            $dataSave['store_id'] = $storeId;
            if(count($dataSave))
            {
                $this->setData($dataSave)->save();
            }
        }
    }

}