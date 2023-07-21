<?php
 
namespace Magebay\Bookingsystem\Model\System\Config;
 
use Magento\Framework\Option\ArrayInterface;
 
class FormatDate implements ArrayInterface
{
 
    /**
     * @return array
     */
    public function toOptionArray()
    {
         return array(
            array(
                'label' => 'mm/dd/yyyy',
                'value' => 'm/d/Y',
            ),
           array(
                'label' => 'mm-dd-yyyy',
                'value' => 'm-d-Y',
            ),
			array(
                'label' => 'dd/mm/yyyy',
                'value' => 'd/m/Y',
            ),
           array(
                'label' => 'dd-mm-yyyy',
                'value' => 'd-m-Y',
            ),
			array(
                'label' => 'yyyy/mm/dd',
                'value' => 'Y/m/d',
            ),
           array(
                'label' => 'yyyy-mm-dd',
                'value' => 'Y-m-d',
            ),
			array(
                'label' => 'yyyy/dd/mm',
                'value' => 'Y/d/m',
            ),
           array(
                'label' => 'yyyy-dd-mm',
                'value' => 'Y-d-m',
            ),
        );
    }
}