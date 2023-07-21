<?php


namespace Daytours\BookingLocked\Model;

use \Daytours\BookingLocked\Api\Data;
use \Daytours\BookingLocked\Api\Data\BookingLockedInterface;
use Daytours\BookingLocked\Api\Data\BookingLockedSearchResultsInterface;
use \Daytours\BookingLocked\Model\ResourceModel\BookingLocked as ResourceModelBookingLocked;
use \Daytours\BookingLocked\Model\ResourceModel\BookingLocked\CollectionFactory as BookingLockedCollectionFactory;
use \Daytours\BookingLocked\Model\BookingLockedFactory;
use \Daytours\BookingLocked\Api\Data\BookingLockedSearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;


class BookingLockedRepository implements \Daytours\BookingLocked\Api\BookingLockedRepositoryInterface
{
    /**
     * @var ResourceModelBookingLocked
     */
    protected $resource;
    /**
     * @var BookingLockedInterface[]
     */
    protected $instances = [];
    /**
     * @var BookingLockedFactory
     */
    private $bookingLockedFactory;
    /**
     * @var BookingLockedCollectionFactory
     */
    private $bookingLockedCollectionFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    /**
     * @var Data\BookingLockedSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * BookingLockedRepository constructor.
     * @param BookingLockedFactory $bookingLockedFactory
     * @param ResourceModelBookingLocked $resource
     * @param BookingLockedCollectionFactory $bookingLockedCollectionFactory
     * @param BookingLockedSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        BookingLockedFactory $bookingLockedFactory,
        ResourceModelBookingLocked $resource,
        BookingLockedCollectionFactory $bookingLockedCollectionFactory,
        BookingLockedSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    )
    {
        $this->resource = $resource;
        $this->bookingLockedFactory = $bookingLockedFactory;
        $this->bookingLockedCollectionFactory = $bookingLockedCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    public function getById($id)
    {
        if( !isset($this->instances[$id]) ){
            $locked = $this->bookingLockedFactory->create();
            $this->resource->load($locked,$id);
            if( !$locked->getId() ){
                throw new NoSuchEntityException(__('Locked date with id "%1 does not exist"',$id));
            }
            $this->instances[$id] = $locked;
        }
        return $this->instances[$id];
    }

    /**
     * @param BookingLockedInterface $locked
     * @return BookingLockedInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\BookingLockedInterface $locked)
    {
        try {
            $this->resource->save($locked);
        }catch (\Exception $exception){
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $locked;
    }
    public function delete(Data\BookingLockedInterface $locked)
    {
        try {
            $this->resource->delete($locked);
        }catch (\Exception $exception){
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $locked;
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
     * @return BookingLockedSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Daytours\BookingLocked\Model\ResourceModel\BookingLocked\Collection $collection */
        $collection = $this->bookingLockedCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \Daytours\BookingLocked\Api\Data\BookingLockedSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    public function lockedDateExist($productId, $date,$calendarNumber)
    {

        /** @var \Daytours\BookingLocked\Model\ResourceModel\BookingLocked\Collection $collection */
        $collection = $this->bookingLockedCollectionFactory->create();
        $collection->addFilter(\Daytours\BookingLocked\Api\Data\BookingLockedInterface::PRODUCT_ID,$productId)
            ->addFilter(\Daytours\BookingLocked\Api\Data\BookingLockedInterface::LOCKED,$date)
            ->addFilter(\Daytours\BookingLocked\Api\Data\BookingLockedInterface::CALENDAR_NUMBER,$calendarNumber);

        /** @var \Daytours\BookingLocked\Model\BookingLocked $locked */
        $locked = $collection->getFirstItem();

        if( $locked->getId() ){
           return false;
        }

        return true;

    }

    public function getLockedByProductId($productId,$calendarNumber)
    {

        /** @var \Daytours\BookingLocked\Model\ResourceModel\BookingLocked\Collection $collection */
        $collection = $this->bookingLockedCollectionFactory->create();
        $collection->addFilter(\Daytours\BookingLocked\Api\Data\BookingLockedInterface::PRODUCT_ID,$productId)
            ->addFilter(\Daytours\BookingLocked\Api\Data\BookingLockedInterface::CALENDAR_NUMBER,$calendarNumber);;

        /** @var \Daytours\BookingLocked\Model\BookingLocked $locked */
        return $locked = $collection->getItems();

    }

}