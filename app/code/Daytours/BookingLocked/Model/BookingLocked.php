<?php

namespace Daytours\BookingLocked\Model;

use Daytours\BookingLocked\Api\Data\BookingLockedInterface;
use Daytours\BookingLocked\Model\ResourceModel\BookingLocked as BookingLockedAlias;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * @method BookingLockedAlias getResource()
 * @method \Daytours\BookingLocked\Model\ResourceModel\BookingLocked\Collection getCollection()
 */
class BookingLocked extends AbstractModel implements BookingLockedInterface, IdentityInterface
{
    const TABLE_NAME        = 'booking_locked_date';
    const CACHE_TAG         = 'daytours_bookinglocked_bookinglocked';
    protected $_cacheTag    = 'daytours_bookinglocked_bookinglocked';
    protected $_eventPrefix = 'daytours_bookinglocked_bookinglocked';

    protected function _construct()
    {
        $this->_init(BookingLockedAlias::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }
    public function getLocked()
    {
        return $this->getData(self::LOCKED);
    }
    public function getCalendarNumber()
    {
        return $this->getData(self::CALENDAR_NUMBER);
    }
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID,$id);
    }
    public function setProductId($product_id)
    {
        return $this->setData(self::PRODUCT_ID,$product_id);
    }
    public function setLocked($date)
    {
        return $this->setData(self::LOCKED,$date);
    }
    public function setCalendarNumber($calendarNumber)
    {
        return $this->setData(self::CALENDAR_NUMBER,$calendarNumber);
    }

}