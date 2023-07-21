<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/

namespace Magebay\Marketplace\Model\System\Config\Vendor;
 
use Magento\Framework\Option\ArrayInterface;
 
class ProductType implements ArrayInterface
{
    /**
     * Get all product type
     *
     * @return array
     */
    public function toOptionArray()
    {
        $types = array('Simple', 'Download', 'Virtual', 'Configurable','Booking');
        $data = array();

        foreach($types as $type)	{
            $data[] = array('label' => $type, 'value' => strtolower($type));
        }

        return $data;
    }
}