<?php

namespace Daytours\LastMinute\Model;

use Magebay\Bookingsystem\Model\BookingsFactory;
use Magebay\Bookingsystem\Helper\BkHelperDate;
use Daytours\LastMinute\Helper\LastMinute;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Daytours\Bookingsystem\Helper\Data as DataBooking;

class Order
{
    /**
     * @var \Magebay\Bookingsystem\Model\BookingsFactory
     **/
    protected $_bookingFactory;

    /**
     * @var \Magebay\Bookingsystem\Helper\BkHelperDate
     **/
    protected $_bkHelperDate;

    /**
     * @var \Daytours\LastMinute\Helper\LastMinute
     **/
    protected $_lastMinuteHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     **/
    protected $_timezone;
    /**
     * @var DataBooking
     */
    private $dataBookingSystem;

    /**
     * Order constructor.
     * @param BookingsFactory $bookingFactory
     * @param BkHelperDate $bkHelperDate
     * @param LastMinute $lastMinuteHelper
     * @param TimezoneInterface $timezone
     * @param DataBooking $dataBookingSystem
     */
    public function __construct(
        BookingsFactory $bookingFactory,
        BkHelperDate $bkHelperDate,
        LastMinute $lastMinuteHelper,
        TimezoneInterface $timezone,
        DataBooking $dataBookingSystem
    )
    {
        $this->_bookingFactory = $bookingFactory;
        $this->_bkHelperDate = $bkHelperDate;
        $this->_lastMinuteHelper = $lastMinuteHelper;
        $this->_timezone = $timezone;
        $this->dataBookingSystem = $dataBookingSystem;
    }

    /**
     * Check if some of the order items is Last Minute
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function isLastMinute($order)
    {
        $items = $order->getAllVisibleItems();
        if (count($items)) {
            foreach ($items as $item) {
                $product = $item->getProduct();

                $requestOptions = $item->getProductOptionByCode('info_buyRequest');

                $checkin = $requestOptions['check_in'];
                $isToday = $this->dataBookingSystem->isToday($checkin);
                $isTomorrow = $this->dataBookingSystem->isTomorrow($checkin);

                if( $this->_lastMinuteHelper->ifHasLastminute($product)  && ($isToday || $isTomorrow )){

                    if( $this->dataBookingSystem->isTomorrow($checkin)  ){
                        return true;
                    }

                    $lastminute_event_start = $product->getLastminuteEventStart();
                    $startEvent = explode(":",$lastminute_event_start);

                    $date = $this->_timezone->date();
                    $currentDateHoureMinute = explode(":",$date->format('H:i'));

                    if( $currentDateHoureMinute[0] <= $startEvent[0] ){
                        if( $currentDateHoureMinute[0] == $startEvent[0] ){
                            if( $currentDateHoureMinute[1] <= $startEvent[1] ){
                                return true;
                            }else{
                                return false;
                            }
                        }
                        return true;
                    }
                    return false;

                }

            }
        }

        return false;
    }
}