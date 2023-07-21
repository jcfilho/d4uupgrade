<?php
 
namespace Daytours\Bookingsystem\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Checkout\Model\Cart as BkCoreCart;
use Magento\Sales\Model\Order as BkCoreOrders;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magebay\Bookingsystem\Helper\BkHelperDate;
use Magebay\Bookingsystem\Model\BookingordersFactory;

class BkOrderHelper extends \Magebay\Bookingsystem\Helper\BkOrderHelper
{
	/**
	* @var Magento\Checkout\Model\Cart
	**/
	protected $_bkCoreCart;
	/**
     *
     * @var Magento\Sales\Model\Order
    */
	protected $_bkCoreOrders;
	/**
     *
     * @var Magento\Backend\Model\Session\Quote
    */
	protected $_quoteSession;
	/**
     * Helper Date
     *
     * @var \Magebay\Bookingsystem\Helper\BkHelperDate
    */
	protected $_bkHelperDate;
	/**
     * Booking order
     *
     * @var Magebay\Bookingsystem\Model\BookingordersFactory;
    */
	protected $_bookingordersFactory;
	public function __construct(
       Context $context,
       BkCoreCart $bkCoreCart,
	   BkCoreOrders $bkCoreOrders,
	   QuoteSession $quoteSession,
	   BkHelperDate $bkHelperDate,
	   BookingordersFactory $bookingordersFactory,
       \Magento\Framework\Stdlib\DateTime\Timezone $timeZone
    ) 
	{
		parent::__construct(
            $context,
           $bkCoreCart,
           $bkCoreOrders,
           $quoteSession,
           $bkHelperDate,
           $bookingordersFactory,
           $timeZone
        );
		$this->_bkCoreCart = $bkCoreCart;
		$this->_bkCoreOrders = $bkCoreOrders;
		$this->_quoteSession = $quoteSession;
	    $this->_bkHelperDate = $bkHelperDate;
	    $this->_bookingordersFactory = $bookingordersFactory;
	    $this->_timeZone = $timeZone;
    }
	/**
	* get request data in carts (getRequestItemOption)
	* @return array $request 
	**/
	function getBkRequestItemOption($itemId,$productId)
	{
		$customOptionsRequest = array();
		$carts = $this->_bkCoreCart;
		$items = $carts->getQuote()->getAllItems();
		foreach($items as $item)
		{
			if($item->getProduct()->getTypeId() != 'booking')
			{
				continue;
			}
			if($item->getId() == $itemId && $item->getProduct()->getId() == $productId)
			{
				$_customOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
				$customOptionsRequest = $_customOptions['info_buyRequest'];
			}
		}
		return $customOptionsRequest;
	}
	/**
	* get total booking item in cart
	* @param $booking item, itemId
	* @return array $arrayItem
	**/
	function getArrayItemIncart($bookingId,$itemId = 0,$isBackend = false)
	{
		$arrayItem = array();
		$customOptionsRequest = array();
		$items = array();
		if(!$isBackend)
		{
			$carts = $this->_bkCoreCart;
			$items = $carts->getQuote()->getAllItems();
		}
		else
		{
			$items = $this->_quoteSession->getQuote()->getAllItems();
		}
		if(count($items))
		{
			$i = 0;
			foreach($items as $item)
			{
				//don't get value of edit current cart item
				if($item->getId() == $itemId)
				{
					continue;
				}
				if($item->getProduct()->getTypeId() != 'booking')
				{
					continue;
				}
				//get request option
				$_customOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
				$customOptionsRequest = $_customOptions['info_buyRequest'];
				//convert date of item in cart
				$cCheckIn = $this->_bkHelperDate->convertFormatDate($customOptionsRequest['check_in']);
				if(isset($customOptionsRequest['check_out']))
				{
					$cCheckOut = $this->_bkHelperDate->convertFormatDate($customOptionsRequest['check_out']);
				}
				else
				{
					$cCheckOut = $cCheckIn;
				}
				$persons = isset($customOptionsRequest['number_persons']) ? $customOptionsRequest['number_persons'] : array();
				//if current bookingId = $bookingId in cart
				if($bookingId == $item->getProduct()->getId())
				{
					$arrayItem[$i]['check_in'] = $cCheckIn;
					$arrayItem[$i]['check_out'] = $cCheckOut;
					$arrayItem[$i]['qty'] = $item->getQty();
					$arrayItem[$i]['number_persons'] = $persons;
					$i++;
				}
			}
		}
		return $arrayItem;
	}

    /**
     * get total booking item in cart
     * @param $booking item, itemId
     * @return array $arrayItem
     **/
    function getArrayItemIncartTwo($bookingId,$itemId = 0,$isBackend = false)
    {
        $arrayItem = array();
        $customOptionsRequest = array();
        $items = array();
        if(!$isBackend)
        {
            $carts = $this->_bkCoreCart;
            $items = $carts->getQuote()->getAllItems();
        }
        else
        {
            $items = $this->_quoteSession->getQuote()->getAllItems();
        }
        if(count($items))
        {
            $i = 0;
            foreach($items as $item)
            {
                //don't get value of edit current cart item
                if($item->getId() == $itemId)
                {
                    continue;
                }
                if($item->getProduct()->getTypeId() != 'booking')
                {
                    continue;
                }
                //get request option
                $_customOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $customOptionsRequest = $_customOptions['info_buyRequest'];
                //convert date of item in cart
                $cCheckIn = $this->_bkHelperDate->convertFormatDate($customOptionsRequest['check_in']);
                $cCheckInTwo = '';
                if( isset($customOptionsRequest['check_in_two']) ){
                    $cCheckInTwo =  $this->_bkHelperDate->convertFormatDate($customOptionsRequest['check_in_two']);
                }
                if(isset($customOptionsRequest['check_out']))
                {
                    $cCheckOut = $this->_bkHelperDate->convertFormatDate($customOptionsRequest['check_out']);
                }
                else
                {
                    $cCheckOut = $cCheckIn;
                }
                if(isset($customOptionsRequest['check_out_two']))
                {
                    $cCheckOutTwo = $this->_bkHelperDate->convertFormatDate($customOptionsRequest['check_out_two']);
                }
                else
                {
                    $cCheckOutTwo = $cCheckInTwo;
                }
                $persons = isset($customOptionsRequest['number_persons']) ? $customOptionsRequest['number_persons'] : array();
                //if current bookingId = $bookingId in cart
                if($bookingId == $item->getProduct()->getId())
                {
                    $arrayItem[$i]['check_in'] = $cCheckIn;
                    $arrayItem[$i]['check_out'] = $cCheckOut;
                    if( !empty($cCheckInTwo) ){
                        $arrayItem[$i]['check_in_two'] = $cCheckInTwo;
                        $arrayItem[$i]['check_out_two'] = $cCheckOutTwo;
                    }
                    $arrayItem[$i]['qty'] = $item->getQty();
                    $arrayItem[$i]['number_persons'] = $persons;
                    $i++;
                }
            }
        }
        return $arrayItem;
    }
	/**
	* get total booking item in cart
	* @param $booking item, itemId
	* @return array $arrayItem
	**/
	function getArrayIntervalItemIncart($bookingId,$itemId = 0,$isBackend = false)
	{
		$arrayItems = array();
		$customOptionsRequest = array();
		$items = array();
		if(!$isBackend)
		{
			$carts = $this->_bkCoreCart;
			$items = $carts->getQuote()->getAllItems();
		}
		else
		{
			$items = $this->_quoteSession->getQuote()->getAllItems();
		}
		if(count($items))
		{
			$i = 0;
			foreach($items as $item)
			{
				//don't get value of edit current cart item
				if($item->getId() == $itemId)
				{
					continue;
				}
				if($item->getProduct()->getTypeId() != 'booking')
				{
					continue;
				}
				//get request option
				$_customOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
				$customOptionsRequest = $_customOptions['info_buyRequest'];
				$cCheckIn =  $this->_bkHelperDate->convertFormatDate($customOptionsRequest['check_in']);
				$intervals = isset($customOptionsRequest['intervals_hours']) ? $customOptionsRequest['intervals_hours'] : array();
				//if current bookingId = $bookingId in cart
				if($bookingId == $item->getProduct()->getId() && count($intervals))
				{
					$arrayItems[$i]['check_in'] = $cCheckIn;
					$arrayItems[$i]['hour_intervals'] = $intervals;
					$arrayItems[$i]['qty'] = $item->getQty();
					$i++;
				}
			}
		}
		return $arrayItems;
	}

    /**
     * get total booking item in cart
     * @param $booking item, itemId
     * @return array $arrayItem
     **/
    function getArrayIntervalItemIncartTwo($bookingId,$itemId = 0,$isBackend = false)
    {
        $arrayItems = array();
        $customOptionsRequest = array();
        $items = array();
        if(!$isBackend)
        {
            $carts = $this->_bkCoreCart;
            $items = $carts->getQuote()->getAllItems();
        }
        else
        {
            $items = $this->_quoteSession->getQuote()->getAllItems();
        }
        if(count($items))
        {
            $i = 0;
            foreach($items as $item)
            {
                //don't get value of edit current cart item
                if($item->getId() == $itemId)
                {
                    continue;
                }
                if($item->getProduct()->getTypeId() != 'booking')
                {
                    continue;
                }
                //get request option
                $_customOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $customOptionsRequest = $_customOptions['info_buyRequest'];
                if( isset($customOptionsRequest['check_in_two']) ){
                    $cCheckIn =  $this->_bkHelperDate->convertFormatDate($customOptionsRequest['check_in_two']);
                    $intervals = isset($customOptionsRequest['intervals_hours_two']) ? $customOptionsRequest['intervals_hours_two'] : array();
                    //if current bookingId = $bookingId in cart
                    if($bookingId == $item->getProduct()->getId() && count($intervals))
                    {
                        $arrayItems[$i]['check_in_two'] = $cCheckIn;
                        $arrayItems[$i]['hour_intervals_two'] = $intervals;
                        $arrayItems[$i]['qty'] = $item->getQty();
                        $i++;
                    }
                }
            }
        }
        return $arrayItems;
    }

	/*
	* get Current Item for Interval in cart
	*/
	function getCurrentIntervalItemIncart($bookingId,$itemId = 0,$isBackend = false)
	{
		$arrayItems = array();
		$customOptionsRequest = array();
		$items = array();
		if(!$isBackend)
		{
			$carts = $this->_bkCoreCart;
			$items = $carts->getQuote()->getAllItems();
		}
		else
		{
			$items = $this->_quoteSession->getQuote()->getAllItems();
		}
		if(count($items))
		{
			foreach($items as $item)
			{
				if($item->getProduct()->getTypeId() != 'booking')
				{
					continue;
				}
				//don't get value of edit current cart item
				if($item->getId() == $itemId)
				{
					//get request option
					$_customOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
					$customOptionsRequest = $_customOptions['info_buyRequest'];
					$cCheckIn =  $this->_bkHelperDate->convertFormatDate($customOptionsRequest['check_in']);
					$intervals = isset($customOptionsRequest['intervals_hours']) ? $customOptionsRequest['intervals_hours'] : array();
					//if current bookingId = $bookingId in cart
					if($bookingId == $item->getProduct()->getId() && count($intervals))
					{
						$arrayItems['check_in'] = $cCheckIn;
						$arrayItems['hour_intervals'] = $intervals;
						$arrayItems['qty'] = $item->getQty();
						break;
					}
				}
			}
		}
		return $arrayItems;
	}

    /*
    * get Current Item for Interval in cart
    */
    function getCurrentIntervalItemIncartTwo($bookingId,$itemId = 0,$isBackend = false)
    {
        $arrayItems = array();
        $customOptionsRequest = array();
        $items = array();
        if(!$isBackend)
        {
            $carts = $this->_bkCoreCart;
            $items = $carts->getQuote()->getAllItems();
        }
        else
        {
            $items = $this->_quoteSession->getQuote()->getAllItems();
        }
        if(count($items))
        {
            foreach($items as $item)
            {
                if($item->getProduct()->getTypeId() != 'booking')
                {
                    continue;
                }
                //don't get value of edit current cart item
                if($item->getId() == $itemId)
                {
                    //get request option
                    $_customOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                    $customOptionsRequest = $_customOptions['info_buyRequest'];
                    if( isset($customOptionsRequest['check_in_two']) ){
                        $cCheckIn =  $this->_bkHelperDate->convertFormatDate($customOptionsRequest['check_in_two']);
                        $intervals = isset($customOptionsRequest['intervals_hours_two']) ? $customOptionsRequest['intervals_hours_two'] : array();
                        //if current bookingId = $bookingId in cart
                        if($bookingId == $item->getProduct()->getId() && count($intervals))
                        {
                            $arrayItems['check_in_two'] = $cCheckIn;
                            $arrayItems['hour_intervals_two'] = $intervals;
                            $arrayItems['qty'] = $item->getQty();
                            break;
                        }
                    }
                }
            }
        }
        return $arrayItems;
    }

	/*
	*  get data from booking order 
	* @param string $strDate, int $bookingId
	* @return int $quantityTotal
	*/
	function getOrderIntervalsTotal($bookingId,$strDate,$strInterTime,$oldOrderItemId = 0)
	{
		$total = 0;
		$bkOrdersModel = $this->_bookingordersFactory->create();
		$orders = $bkOrdersModel->getCollection()
				->addFieldToSelect(array('bkorder_quantity_interval','bkorder_id','bkorder_order_id','bkorder_qt_item_id'))
				->addFieldToFilter('bkorder_booking_id',$bookingId)
				->addFieldToFilter('bkorder_room_id',0)
				//->addFieldToFilter('bkorder_interval_time',array('finset'=>$strInterTime))
				->addFieldToFilter('bkorder_check_in',$strDate);
		$coreModel = $this->_bkCoreOrders;
		foreach($orders as $order)
		{
			$defaultOrder = $coreModel->load($order->getBkorderOrderId());
			if($defaultOrder->getId())
			{
				if($defaultOrder->getStatus() == 'canceled' || $defaultOrder->getStatus() == 'closed')
				{
					continue;
				}
			}
			else
			{
				continue;
			}
			if($oldOrderItemId > 0 && $oldOrderItemId == $order->getBkorderQtItemId())
			{
				continue;
			}
			$total += $order->getBkorderQuantityInterval();
		}
		return 	$total;
	}

    /*
    *  get data from booking order
    * @param string $strDate, int $bookingId
    * @return int $quantityTotal
    */
    function getOrderIntervalsTotalTwo($bookingId,$strDate,$strInterTime,$oldOrderItemId = 0)
    {
        $total = 0;
        $bkOrdersModel = $this->_bookingordersFactory->create();
        $orders = $bkOrdersModel->getCollection()
            ->addFieldToSelect(array('bkorder_quantity_interval_two','bkorder_id','bkorder_order_id','bkorder_qt_item_id'))
            ->addFieldToFilter('bkorder_booking_id',$bookingId)
            ->addFieldToFilter('bkorder_room_id',0)
            ->addFieldToFilter('bkorder_interval_time_two',array('finset'=>$strInterTime))
            ->addFieldToFilter('bkorder_check_in_two',$strDate);
        $coreModel = $this->_bkCoreOrders;
        foreach($orders as $order)
        {
            $defaultOrder = $coreModel->load($order->getBkorderOrderId());
            if($defaultOrder->getId())
            {
                if($defaultOrder->getStatus() == 'canceled' || $defaultOrder->getStatus() == 'closed')
                {
                    continue;
                }
            }
            else
            {
                continue;
            }
            if($oldOrderItemId > 0 && $oldOrderItemId == $order->getBkorderQtItemId())
            {
                continue;
            }
            $total += $order->getBkorderQuantityIntervalTwo();
        }
        return 	$total;
    }

	/**
	* get total booking item in cart
	* @param $booking item, string $strDay has format is Y-m-d,$itemId
	* @return int $total
	**/
	function getTotalInterItemInCart($bookingId,$strDay,$strInterTime,$itemId = 0,$isBackend = false)
	{
		$total = 0;
		$arrayItem = $this->getArrayIntervalItemIncart($bookingId,$itemId,$isBackend);
		foreach($arrayItem as $item)
		{
			if($strDay == $item['check_in'] && in_array($strInterTime,$item['hour_intervals']))
			{
				$total += $item['qty'];
			}
		}
		return $total;
	}

    /**
     * get total booking item in cart two calendar
     * @param $booking item, string $strDay has format is Y-m-d,$itemId
     * @return int $total
     **/
    function getTotalInterItemInCartTwo($bookingId,$strDay,$strInterTime,$itemId = 0,$isBackend = false)
    {
        $total = 0;
        $arrayItem = $this->getArrayIntervalItemIncartTwo($bookingId,$itemId,$isBackend);
        foreach($arrayItem as $item)
        {
            if($strDay == $item['check_in_two'] && in_array($strInterTime,$item['hour_intervals_two']))
            {
                $total += $item['qty'];
            }
        }
        return $total;
    }
	/**
	* get total booking item in cart
	* @param int $bookingId, $roomId, itemId
	* @return array $arrayItem
	**/
	function getRoomArrayItemIncart($bookingId,$roomId,$itemId = 0)
	{
		$arrayItem = array();
		$carts = $this->_bkCoreCart;
		$items = $carts->getQuote()->getAllItems();
		if(count($carts))
		{
			$i = 0;
			foreach($items as $item)
			{
				//don't get value of edit current cart item
				if($item->getId() == $itemId)
				{
					continue;
				}
				if($item->getProduct()->getTypeId() != 'booking')
				{
					continue;
				}
				//get request option
				$_customOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
				$customOptionsRequest = $_customOptions['info_buyRequest'];
				//convert date of item in cart
				if(isset($customOptionsRequest['room_check_in']))
				{
					$cCheckIn = $this->_bkHelperDate->convertFormatDate($customOptionsRequest['room_check_in']);
					$cCheckOut = $this->_bkHelperDate->convertFormatDate($customOptionsRequest['room_check_out']);
					//if current bookingId = $bookingId in cart
					if($bookingId == $item->getProduct()->getId() && $customOptionsRequest['room_id'] == $roomId)
					{
						$arrayItem[$i]['check_in'] = $cCheckIn;
						$arrayItem[$i]['check_out'] = $cCheckOut;
						$arrayItem[$i]['qty'] = $item->getQty();
						$i++;
					}
				}
			}
		}
		return $arrayItem;
	}
    function getArrayItemsInOrder($bookingId, $roomId = 0, $oldOrderItemId = 0)
    {
        // $arraySelect = array('check_in','check_out','qty');
        $intCurrentTime = $this->_timeZone->scopeTimeStamp();
        $currDate = date('Y-m-d',$intCurrentTime);
        $arSelectOrder = array();
        $arAttributeConditions = array();
        $condition = 'booking_id = '.$bookingId;
        $arrayBooking = array('bkorder_check_in','bkorder_check_out','bkorder_qty','bkorder_order_id','bkorder_qt_item_id');
        $totalOrder = 0;
        $orderModel = $this->_bookingordersFactory->create();
        $orders = $orderModel->getCollection()
            ->addFieldToSelect($arrayBooking)
            ->addFieldToFilter('bkorder_booking_id',$bookingId)
            ->addFieldToFilter('bkorder_check_out',array('gteq'=>$currDate))
            ->addFieldToFilter('bkorder_room_id',$roomId);
        $arrayOrders = array();
        if(count($orders))
        {
            $i = 0;
            foreach($orders as $order)
            {
                $bkCoreModel = $this->_bkCoreOrders;
                $defaultOrderCollection = $bkCoreModel->getCollection()
                    ->addFieldToFilter('entity_id',$order->getBkorderOrderId());
                // $defaultOrder = $bkCoreModel->load($order->getBkorderOrderId());
                $defaultOrder = $defaultOrderCollection->getFirstItem();
                if($defaultOrder->getId())
                {
                    if($defaultOrder->getStatus() == 'canceled' || $defaultOrder->getStatus() == 'closed')
                    {
                        continue;
                    }
                }
                else
                {
                    continue;
                }
                if($oldOrderItemId > 0)
                {
                    if($order->getBkorderQtItemId() == $oldOrderItemId)
                    {
                        continue;
                    }
                }
                $arrayOrders[$i]['check_in'] = $order->getBkorderCheckIn();
                $arrayOrders[$i]['check_out'] = $order->getBkorderCheckOut();
                $arrayOrders[$i]['qty'] = $order->getBkorderQty();
                $i++;
            }
        }
        return $arrayOrders;
    }

    function getArrayItemsInOrderCalendarTwo($bookingId, $roomId = 0, $oldOrderItemId = 0)
    {
        // $arraySelect = array('check_in','check_out','qty');
        $intCurrentTime = $this->_timeZone->scopeTimeStamp();
        $currDate = date('Y-m-d',$intCurrentTime);
        $arSelectOrder = array();
        $arAttributeConditions = array();
        $condition = 'booking_id = '.$bookingId;
        $arrayBooking = array('bkorder_check_in_two','bkorder_check_out_two','bkorder_qty_two','bkorder_order_id','bkorder_qt_item_id');
        $totalOrder = 0;
        $orderModel = $this->_bookingordersFactory->create();
        $orders = $orderModel->getCollection()
            ->addFieldToSelect($arrayBooking)
            ->addFieldToFilter('bkorder_booking_id',$bookingId)
            ->addFieldToFilter('bkorder_check_out_two',array('gteq'=>$currDate))
            ->addFieldToFilter('bkorder_room_id',$roomId);
        $arrayOrders = array();
        if(count($orders))
        {
            $i = 0;
            foreach($orders as $order)
            {
                $bkCoreModel = $this->_bkCoreOrders;
                $defaultOrderCollection = $bkCoreModel->getCollection()
                    ->addFieldToFilter('entity_id',$order->getBkorderOrderId());
                // $defaultOrder = $bkCoreModel->load($order->getBkorderOrderId());
                $defaultOrder = $defaultOrderCollection->getFirstItem();
                if($defaultOrder->getId())
                {
                    if($defaultOrder->getStatus() == 'canceled' || $defaultOrder->getStatus() == 'closed')
                    {
                        continue;
                    }
                }
                else
                {
                    continue;
                }
                if($oldOrderItemId > 0)
                {
                    if($order->getBkorderQtItemId() == $oldOrderItemId)
                    {
                        continue;
                    }
                }
                $arrayOrders[$i]['check_in'] = $order->getBkorderCheckInTwo();
                $arrayOrders[$i]['check_out'] = $order->getBkorderCheckOutTwo();
                $arrayOrders[$i]['qty'] = $order->getBkorderQtyTwo();
                $i++;
            }
        }
        return $arrayOrders;
    }

    function getArrayIntervalsInOrders($bookingId)
    {
        $orderModel = $this->_bookingordersFactory->create();
        $arSelectOrder = array('bkorder_check_in','bkorder_check_out','bkorder_qty','bkorder_quantity_interval','bkorder_interval_time','bkorder_order_id');
        $orders = $orderModel->getCollection()
            ->addFieldToSelect($arSelectOrder)
            ->addFieldToFilter('bkorder_booking_id',$bookingId)
            ->addFieldToFilter('bkorder_room_id',0);
        $arOrder = array();
        if(count($orders))
        {
            $i = 0;
            $bkCoreModel = $this->_bkCoreOrders;
            foreach($orders as $order)
            {
                $defaultOrder = $bkCoreModel->load($order->getBkorderOrderId());
                if($defaultOrder->getId())
                {
                    if($defaultOrder->getStatus() == 'canceled' || $defaultOrder->getStatus() == 'closed')
                    {
                        continue;
                    }
                }
                else
                {
                    continue;
                }
                $tempQty = 0;
                $tempInters = explode(',',$order->getBkorderIntervalTime());
                if(count($tempInters))
                {
                    foreach($tempInters as $tempInter)
                    {
                        $tempQty += $order->getBkorderQuantityInterval();
                    }
                }
                $arOrder[$i]['check_in'] = $order->getBkorderCheckIn();
                $arOrder[$i]['check_out'] = $order->getBkorderCheckIn();
                $arOrder[$i]['qty'] = $tempQty ;
                $i++;
            }
        }
        return $arOrder;
    }

    function getArrayIntervalsInOrdersCalendarTwo($bookingId)
    {
        $orderModel = $this->_bookingordersFactory->create();
        $arSelectOrder = array('bkorder_check_in_two','bkorder_check_out_two','bkorder_qty_two','bkorder_quantity_interval_two','bkorder_interval_time_two','bkorder_order_id');
        $orders = $orderModel->getCollection()
            ->addFieldToSelect($arSelectOrder)
            ->addFieldToFilter('bkorder_booking_id',$bookingId)
            ->addFieldToFilter('bkorder_room_id',0);
        $arOrder = array();
        if(count($orders))
        {
            $i = 0;
            $bkCoreModel = $this->_bkCoreOrders;
            foreach($orders as $order)
            {
                $defaultOrder = $bkCoreModel->load($order->getBkorderOrderId());
                if($defaultOrder->getId())
                {
                    if($defaultOrder->getStatus() == 'canceled' || $defaultOrder->getStatus() == 'closed')
                    {
                        continue;
                    }
                }
                else
                {
                    continue;
                }
                $tempQty = 0;
                $tempInters = explode(',',$order->getBkorderIntervalTimeTwo());
                if(count($tempInters))
                {
                    foreach($tempInters as $tempInter)
                    {
                        $tempQty += $order->getBkorderQuantityIntervalTwo();
                    }
                }
                $arOrder[$i]['check_in'] = $order->getBkorderCheckInTwo();
                $arOrder[$i]['check_out'] = $order->getBkorderCheckInTwo();
                $arOrder[$i]['qty'] = $tempQty ;
                $i++;
            }
        }
        return $arOrder;
    }

}
 