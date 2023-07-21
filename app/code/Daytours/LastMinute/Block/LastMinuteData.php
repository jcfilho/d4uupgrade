<?php
/**
 * Created by PhpStorm.
 * User: jose
 * Date: 9/12/18
 * Time: 5:10 PM
 */

namespace Daytours\LastMinute\Block;

use \Magento\Framework\View\Element\Template;
use \Magebay\Bookingsystem\Model\BookingsFactory;
use \Magento\Framework\Registry;

class LastMinuteData extends Template
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
    /**
     * @var \Daytours\LastMinute\Helper\LastMinute
     */
    private $lastMinuteHelper;

    public function __construct(
        \Magebay\Bookingsystem\Helper\Data $dataBookingSystem,
        BookingsFactory $bookingFactory,
        Registry $registry,
        \Daytours\LastMinute\Helper\LastMinute $lastMinuteHelper,
        Template\Context $context,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->dataBookingSystem = $dataBookingSystem;
        $this->bookingFactory = $bookingFactory;
        $this->registry = $registry;
        $this->lastMinuteHelper = $lastMinuteHelper;
    }

    public function ifHasLastminute(){
        return $this->lastMinuteHelper->ifHasLastminute();
    }

    /**
     * @return false|string
     */
    public function getJsonData(){
        $product = $this->registry->registry('product');
        $arg = [
            'lastminute_event_start' => $product->getLastminuteEventStart()
        ];
//        $arg = [
//            'lastminute_event_start' => '11:28'
//        ];
        return json_encode($arg);
    }
}