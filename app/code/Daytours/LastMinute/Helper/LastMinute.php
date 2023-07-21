<?php

namespace Daytours\LastMinute\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use \Magebay\Bookingsystem\Model\BookingsFactory;
use \Magento\Framework\Registry;

class LastMinute extends AbstractHelper
{

    /**
     * @var \Magebay\Bookingsystem\Helper\Data
     */
    private $dataBookingSystem;
    /**
     * @var BookingsFactory
     */
    private $bookingFactory;
    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Context $context,
        \Magebay\Bookingsystem\Helper\Data $dataBookingSystem,
        BookingsFactory $bookingFactory,
        Registry $registry
    )
    {
        parent::__construct($context);
        $this->dataBookingSystem = $dataBookingSystem;
        $this->bookingFactory = $bookingFactory;
        $this->registry = $registry;
    }

    /**
     * Check if giving date is within a range of dates
     *
     * @param string $date
     * @param string $startDate
     * @param string $endDate
     * @return bool
     */
    public function isWithinRange($date, $startDate, $endDate)
    {
        $date = new \DateTime($date);
        $startDate = new \DateTime($startDate);
        $endDate = new \DateTime($endDate);

        return $date >= $startDate && $date < $endDate;
    }

    public function ifHasLastminute($product = null){
        $enabled = $this->dataBookingSystem->getFieldSetting('bookingsystem/setting/enable');
        if( $enabled ){

            if( !$product ){
                $product = $this->registry->registry('product');
            }

            $bookingModel = $this->bookingFactory->create();
            $booking = $bookingModel->getBooking($product->getId());

            if ($booking->getId() && $product->getTypeId() == 'booking' && $product->getLastminuteApply()){
                return true;
            }
        }

        return false;
    }

}