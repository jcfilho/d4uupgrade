<?php


namespace Daytours\BookingLocked\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface BookingLockedRepositoryInterface
{
    /**
     * Save Locked
     *
     * @param \Daytours\BookingLocked\Api\Data\BookingLockedInterface $locked
     * @return \Daytours\BookingLocked\Api\Data\BookingLockedInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Daytours\BookingLocked\Api\Data\BookingLockedInterface $locked);

    /**
     * Retrieve locked
     *
     * @param int $id
         * @return \Daytours\BookingLocked\Api\Data\BookingLockedInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve faqs matching the specified criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Daytours\BookingLocked\Api\Data\BookingLockedSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete faq
     *
     * @param \Daytours\BookingLocked\Api\Data\BookingLockedInterface $locked
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Daytours\BookingLocked\Api\Data\BookingLockedInterface $locked);

    /**
     * Delete locked by ID
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);

    /**
     * get Locked date by productId and date
     *
     * @param int $productId
     * @param string $date
     * @param int $calendarNumber
     * @return mixed
     */
    public function lockedDateExist($productId,$date,$calendarNumber);

    /**
     * get Locked date by productId
     *
     * @param int $productId
     * @param int $calendarNumber
     * @return mixed
     */
    public function getLockedByProductId($productId,$calendarNumber);
}