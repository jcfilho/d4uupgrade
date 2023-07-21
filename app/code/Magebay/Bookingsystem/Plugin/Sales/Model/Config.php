<?php
namespace Magebay\Bookingsystem\Plugin\Sales\Model;

class Config
{
	public function afterGetAvailableProductTypes($subject, $result)
    {
		$result[] = 'booking';
        return $result;
    }
}