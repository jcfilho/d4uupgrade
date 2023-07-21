<?php


namespace Daytours\BookingLocked\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface BookingLockedSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get data list.
     *
     * @return \Daytours\BookingLocked\Api\Data\BookingLockedInterface[]
     */
    public function getItems();

    /**
     * Set data list.
     *
     * @param \Daytours\BookingLocked\Api\Data\BookingLockedInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}