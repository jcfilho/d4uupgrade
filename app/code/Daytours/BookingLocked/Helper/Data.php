<?php
/**
 * Author: Jose Carlos Filho
 * Date:   2020-02-12 21:15
 * Project: daytours4u
 * email: josecarlos.filhov@gmail.com
 **/


namespace Daytours\BookingLocked\Helper;


use Daytours\BookingLocked\Api\BookingLockedRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Daytours\BookingLocked\Api\Data\BookingLockedInterface;

class Data extends AbstractHelper
{
    /**
     * @var BookingLockedRepositoryInterface
     */
    private $bookingLockedRepository;

    /**
     * Data constructor.
     * @param Context $context
     * @param BookingLockedRepositoryInterface $bookingLockedRepository
     */
    public function __construct(
         Context $context,
        BookingLockedRepositoryInterface $bookingLockedRepository
     )
     {
         parent::__construct($context);
         $this->bookingLockedRepository = $bookingLockedRepository;
     }

     private function _processDates($lockedDates){
         $resultLockedDates = [];
         foreach ($lockedDates as $lockedDate){
             $resultLockedDates[] = $lockedDate->getData('locked_date');
         }
         return $resultLockedDates;
     }

    public function getLockedDatesByProductIdCalendarOne($productId){
        $lockedDates =  $this->bookingLockedRepository->getLockedByProductId($productId,BookingLockedInterface::CALENDAR_ONE);
        return $this->_processDates($lockedDates);
    }

    public function getLockedDatesByProductIdCalendarTwo($productId){
        $lockedDates = $this->bookingLockedRepository->getLockedByProductId($productId,BookingLockedInterface::CALENDAR_TWO);
        return $this->_processDates($lockedDates);
    }

}