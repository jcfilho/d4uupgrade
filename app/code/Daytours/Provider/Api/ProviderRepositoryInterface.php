<?php


namespace Daytours\Provider\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface ProviderRepositoryInterface
{
    /**
     * Save Provider
     *
     * @param \Daytours\Provider\Api\Data\ProviderInterface $provider
     * @return \Daytours\Provider\Api\Data\ProviderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Daytours\Provider\Api\Data\ProviderInterface $provider);

    /**
     * Retrieve Provider
     *
     * @param int $id
         * @return \Daytours\Provider\Api\Data\ProviderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve Provider matching the specified criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Daytours\Provider\Api\Data\ProviderSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Provider
     *
     * @param \Daytours\Provider\Api\Data\ProviderInterface $provider
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Daytours\Provider\Api\Data\ProviderInterface $provider);

    /**
     * Delete Provider by ID
     *
     * @param int $providerId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($providerId);
}