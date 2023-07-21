<?php

namespace Magebay\Bookingsystem\Model\System\Config;

use Magento\Framework\Option\ArrayInterface;

class Startday implements ArrayInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'label' => 'Sunday',
                'value' => '0',
            ),
            array(
                'label' => 'Monday',
                'value' => '1',
            ),
            array(
                'label' => 'Tuesday',
                'value' => '2',
            ),
            array(
                'label' => 'Wednesday',
                'value' => '3',
            ),
            array(
                'label' => 'Thursday',
                'value' => '4',
            ),
            array(
                'label' => 'Friday',
                'value' => '5',
            ),
            array(
                'label' => 'Saturday',
                'value' => '6',
            ),
        );
    }
}