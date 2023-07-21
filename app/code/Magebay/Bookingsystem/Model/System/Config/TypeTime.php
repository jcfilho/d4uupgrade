<?php
 
namespace Magebay\Bookingsystem\Model\System\Config;
 
use Magento\Framework\Option\ArrayInterface;
 
class TypeTime implements ArrayInterface
{
 
    /**
     * @return array
     */
    public function toOptionArray()
    {
         return array(
            array(
                'label' => '12 hours (AM/PM)',
                'value' => '1',
            ),
           array(
                'label' => '24 hours',
                'value' => '2',
            )
        );
    }
}