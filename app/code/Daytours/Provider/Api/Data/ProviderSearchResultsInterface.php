<?php


namespace Daytours\Provider\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ProviderSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get data list.
     *
     * @return \Daytours\Provider\Api\Data\ProviderInterface[]
     */
    public function getItems();

    /**
     * Set data list.
     *
     * @param \Daytours\Provider\Api\Data\ProviderInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}