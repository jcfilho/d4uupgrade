<?php

namespace Daytours\Bookingsystem\Observer\Frontend;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Json\Helper\Data as JsonHelperData;
use Magebay\Bookingsystem\Helper\BkCustomOptions;

class SalesModelServiceQuoteSubmitBeforeObserver extends \Magebay\Bookingsystem\Observer\Frontend\SalesModelServiceQuoteSubmitBeforeObserver
{
    private $quoteItems = [];
    private $quote = null;
    private $order = null;
    /**
     * @var \Daytours\Bookingsystem\Helper\BkCustomOptions;
     **/
    protected $_bkCustomOptions;
    /**
     * @var JsonHelperData
     */
    private $jsonHelper;

    /**
     * Add order information into GA block to render on checkout success pages
     *
     * @param BkCustomOptions $bkCustomOptions
     * @param JsonHelperData $jsonHelper
     */
    public function __construct(
        BkCustomOptions $bkCustomOptions,
        JsonHelperData $jsonHelper
    )
    {
        parent::__construct($bkCustomOptions);
        $this->_bkCustomOptions = $bkCustomOptions;
        $this->jsonHelper = $jsonHelper;
    }
    public function execute(EventObserver $observer)
    {
        $this->quote = $observer->getQuote();
        $this->order = $observer->getOrder();
        // can not find a equivalent event for sales_convert_quote_item_to_order_item
        /* @var  \Magento\Sales\Model\Order\Item $orderItem */
        foreach($this->order->getItems() as $key => $orderItem)
        {
            if(!$orderItem->getParentItemId() && $orderItem->getProductType() == 'booking')
            {
                $options = [];
                $requestOptions = $orderItem->getProductOptionByCode('info_buyRequest');
                $_product = $orderItem->getProduct();
                $quote = $this->quote->getItems()[$key];
                $additionalOptions = [];
                if($_productFromQuote = $quote->getProduct()->getCustomOptions()){
                    if( !empty($_productFromQuote['additional_options']) ){
                        if( !empty($_productFromQuote['additional_options']->getData('value')) ){
                            $_productFromQuoteAdditionalOptions = $_productFromQuote['additional_options']->getData()['value'];
                            $additionalOptions = $this->jsonHelper->jsonDecode($_productFromQuoteAdditionalOptions);
                        }
                    }
                }

                $optionsMagentoDefault = $orderItem->getProductOptions();
                $options['info_buyRequest'] = $requestOptions;
                $options['additional_options'] = $additionalOptions;
                $options = array_merge($optionsMagentoDefault, $options);
                $orderItem->setProductOptions($options);
            }
        }
    }
}