<?php


namespace Daytours\Provider\Model;

use \Daytours\Provider\Api\Data;
use \Daytours\Provider\Api\Data\ProviderInterface;
use Daytours\Provider\Api\Data\ProviderSearchResultsInterface;
use \Daytours\Provider\Model\ResourceModel\Provider as ResourceModelProvider;
use \Daytours\Provider\Model\ResourceModel\Provider\CollectionFactory as ProviderCollectionFactory;
use \Daytours\Provider\Model\ProviderFactory;
use \Daytours\Provider\Api\Data\ProviderSearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;


class ProviderRepository implements \Daytours\Provider\Api\ProviderRepositoryInterface
{
    /**
     * @var ResourceModelProvider
     */
    protected $resource;
    /**
     * @var ProviderInterface[]
     */
    protected $instances = [];
    /**
     * @var ProviderFactory
     */
    private $ProviderFactory;
    /**
     * @var ProviderCollectionFactory
     */
    private $ProviderCollectionFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    /**
     * @var Data\ProviderSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * ProviderRepository constructor.
     * @param ProviderFactory $ProviderFactory
     * @param ResourceModelProvider $resource
     * @param ProviderCollectionFactory $ProviderCollectionFactory
     * @param ProviderSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ProviderFactory $ProviderFactory,
        ResourceModelProvider $resource,
        ProviderCollectionFactory $ProviderCollectionFactory,
        ProviderSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    )
    {
        $this->resource = $resource;
        $this->ProviderFactory = $ProviderFactory;
        $this->ProviderCollectionFactory = $ProviderCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    public function getById($id)
    {
        if( !isset($this->instances[$id]) ){
            $provider = $this->ProviderFactory->create();
            $this->resource->load($provider,$id);
            if( !$provider->getId() ){
                throw new NoSuchEntityException(__('Provider date with id "%1 does not exist"',$id));
            }
            $this->instances[$id] = $provider;
        }
        return $this->instances[$id];
    }

    public function getByEmail($email)
    {
        $provider = $this->ProviderFactory->create();
        $this->resource->load($provider,$email,'email');

        if( !$provider->getId() ){
            throw new NoSuchEntityException(__('Provider date with email "%1 does not exist"',$email));
        }

        return $provider;
    }

    /**
     * @param ProviderInterface $provider
     * @return ProviderInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\ProviderInterface $provider)
    {
        try {
            $this->resource->save($provider);
        }catch (\Exception $exception){
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $provider;
    }
    public function delete(Data\ProviderInterface $provider)
    {
        try {
            $this->resource->delete($provider);
        }catch (\Exception $exception){
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $provider;
    }

    /**
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return ProviderSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Daytours\Provider\Model\ResourceModel\Provider\Collection $collection */
        $collection = $this->ProviderCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \Daytours\Provider\Api\Data\ProviderSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}