<?php
 
namespace Magebay\Bookingsystem\Model\System\Config;
 
use Magento\Framework\Option\ArrayInterface;
 
class OrderStatus implements ArrayInterface
{
    const ORDER_PEDDING  = 'pending';
    const ORDER_COMPLETE  = 'complete';
    const ORDER_CANCELED   = 'canceled';
    const ORDER_PROCESSING  = 'processing';
    const ORDER_CLOSED = 'closed';
 
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            self::ORDER_PEDDING => __('Pending'),
            self::ORDER_COMPLETE => __('Complete'),
            self::ORDER_CANCELED => __('Canceled'),
            self::ORDER_PROCESSING => __('Processing'),
            self::ORDER_CLOSED => __('closed')
        ];
 
        return $options;
    }
}