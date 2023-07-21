<?php

namespace Daytours\BookingLocked\Api\Data;

interface BookingLockedInterface
{
    const ENTITY_ID         = 'entity_id';
    const PRODUCT_ID        = 'booking_product_id';
    const LOCKED            = 'locked_date';
    const CALENDAR_NUMBER   = 'calendar_number';
    const CALENDAR_ONE      = 1;
    const CALENDAR_TWO      = 2;

    /**
     * Get Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);
    /**
     * Get Product id
     *
     * @return int|null
     */
    public function getProductId();
    /**
     * Set Product id
     *
     * @param int $product_id
     * @return $this
     */
    public function setProductId($product_id);
    /**
     * Get Locked date
     *
     * @return string|null
     */
    public function getLocked();
    /**
     * Set Product id
     *
     * @param string $date
     * @return $this
     */
    public function setLocked($date);

    /**
     * Get Calendar Number
     *
     * @return string|null
     */
    public function getCalendarNumber();
    /**
     * Set Calendar Number
     *
     * @param string $calendarNumber
     * @return $this
     */
    public function setCalendarNumber($calendarNumber);


}