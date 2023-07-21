<?php
/**
 * Author: Jose Carlos Filho
 * Date:   2020-02-03 18:55
 * Project: daytours4u
 * email: josecarlos.filhov@gmail.com
 **/


namespace Daytours\BookingLocked\Block\Adminhtml;

use Daytours\BookingLocked\Api\Data\BookingLockedInterface;
use Magento\Backend\Block\Template;

use \Daytours\BookingLocked\Api\BookingLockedRepositoryInterface;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Framework\Api\FilterBuilder;
use \Magento\Framework\Api\Search\FilterGroupBuilder;
use \Magento\Framework\Api\SortOrderBuilder;

class ListInfoLocked extends Template
{

    /**
     * @var \Daytours\BookingLocked\Api\BookingLockedRepositoryInterface
     */
    private $lockedRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;
    /**
     * @var \Magento\Framework\Api\Search\FilterGroupBuilder
     */
    private $filterGroupBuilder;
    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * ListInfoLocked constructor.
     * @param BookingLockedRepositoryInterface $lockedRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        BookingLockedRepositoryInterface $lockedRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        SortOrderBuilder $sortOrderBuilder,
        Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->lockedRepository = $lockedRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    public function getAllLockedDates($productId,$calendarNumber){
//        $filterProductId = $this->filterBuilder
//            ->setField(BookingLockedInterface::PRODUCT_ID)
//            ->setConditionType('eq')
//            ->setValue($productId)
//            ->create();
//
//        $filterCalendarNumber = $this->filterBuilder
//            ->setField(BookingLockedInterface::CALENDAR_NUMBER)
//            ->setConditionType('eq')
//            ->setValue($calendarNumber)
//            ->create();
//
        $order = $this->sortOrderBuilder
            ->setField(BookingLockedInterface::LOCKED)
            ->setAscendingDirection()
            ->create();
//
//        $filterGroup = $this->filterGroupBuilder
//            ->addFilter($filterProductId)
//            ->addFilter($filterCalendarNumber)
//            ->create();

        $searchCriteriaBuilder = $this->searchCriteriaBuilder
            ->addFilter(BookingLockedInterface::PRODUCT_ID,$productId)
            ->addFilter(BookingLockedInterface::CALENDAR_NUMBER,$calendarNumber)
//            ->setFilterGroups([$filterGroup])
            ->setSortOrders([$order])
            ->create();

        return $this->lockedRepository->getList($searchCriteriaBuilder);
    }

    public function getCalendarOne(){
        return BookingLockedInterface::CALENDAR_ONE;
    }

    public function getCalendarTwo(){
        return BookingLockedInterface::CALENDAR_TWO;
    }

}