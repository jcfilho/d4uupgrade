<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Model\System\Config\Email;
 
use Magento\Framework\Option\ArrayInterface;
 
class WithdrawAdmin implements ArrayInterface
{
    /**
     * Get all product type
     *
     * @return array
     */
    public function toOptionArray()
    {
        $types = array('Email Withdraw Admin (Default)'=>'marketplace_general_email_withdraw_admin');
        $data = array();

        foreach($types as $label => $value)	{
            $data[] = array('label' => $label, 'value' => strtolower($value));
        }

        return $data;
    }
}