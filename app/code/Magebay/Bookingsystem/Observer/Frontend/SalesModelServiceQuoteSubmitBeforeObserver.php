<?php

namespace Magebay\Bookingsystem\Observer\Frontend;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SalesModelServiceQuoteSubmitBeforeObserver implements ObserverInterface
{
    private $quoteItems = [];
    private $quote = null;
    private $order = null;
    protected $_bkCustomOptions;
    /**
     * Add order information into GA block to render on checkout success pages
     *
     * @param EventObserver $observer
     * @return void
     */
    public function __construct(
        \Magebay\Bookingsystem\Helper\BkCustomOptions $bkCustomOptions
    )
    {
        $this->_bkCustomOptions = $bkCustomOptions;
    }
    public function execute(EventObserver $observer)
    {
        $this->quote = $observer->getQuote();
        $this->order = $observer->getOrder();
        // can not find a equivalent event for sales_convert_quote_item_to_order_item
        /* @var  \Magento\Sales\Model\Order\Item $orderItem */
        foreach($this->order->getItems() as $orderItem)
        {
            if(!$orderItem->getParentItemId() && $orderItem->getProductType() == 'booking')
            {
                $requestOptions = $orderItem->getProductOptionByCode('info_buyRequest');
                $_product = $orderItem->getProduct();
                $aradditionalOptions = $this->_bkCustomOptions->createExtractOptions($_product,$requestOptions);
                $options['info_buyRequest'] = $requestOptions;
                $options['additional_options'] = $aradditionalOptions['bk_options'];
                $orderItem->setProductOptions($options);
            }
        }
    }
}