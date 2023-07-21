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
 
class Position implements ArrayInterface
{
    /**
     * Get positions of lastest news block
     *
     * @return array
     */
    public function toOptionArray()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $setOptionArray = $objectManager->create('Magebay\Marketplace\Helper\Data')->getOptionSetGroup();
        
        $data = array();
        
        foreach($setOptionArray as $k=>$v){

            $data[] = array('label' => $v['label'], 'value' => strtolower($v['value']));
        }

        return $data;
    }
}