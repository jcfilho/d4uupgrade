<?php

use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/app/bootstrap.php';

$params = $_SERVER;

$bootstrap = Bootstrap::create(BP, $params);

$obj = $bootstrap->getObjectManager();

$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

/**
 * @var \Daytours\BookingLocked\Model\BookingLocked $lockedFactory
 * @var \Daytours\BookingLocked\Api\BookingLockedRepositoryInterface $lockedRepository
 */

$lockedRepository = $obj->get('Daytours\BookingLocked\Api\BookingLockedRepositoryInterface');
$lockedFactory = $obj->get('Daytours\BookingLocked\Model\BookingLocked');



$searchCriterial = $obj->get('\Magento\Framework\Api\SearchCriteriaBuilder');
$filterBuilder = $obj->get('\Magento\Framework\Api\FilterBuilder');
$sortOrderBuilder = $obj->get('\Magento\Framework\Api\SortOrderBuilder');

$filters[] = $filterBuilder
    ->setConditionType('like')
    ->setField('booking_product_id')
    ->setValue(3)
    ->create();

$searchCriterial->addFilters($filters);
$searchCriterial->addSortOrder(
    $sortOrderBuilder
        ->setField('entity_id')
        ->setDirection(SortOrder::SORT_DESC)
        ->create()
);


$searchCriterial->setPageSize(4);
$searchCriterial->setCurrentPage(1);
$faqs = $lockedRepository->getList($searchCriterial);


//get one item
//$data = $lockedRepository->getById(2);

//delete one item
//$data = $lockedRepository->deleteById(2);

$asd = 'a';

//save
//$lockedFactory->setLocked('2019-08-08');
//$lockedFactory->setProductId(41);
//$lockedRepository->save($lockedFactory);




?>