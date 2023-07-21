<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebay\Marketplace\Ui\Component\Listing\Column\Seller\Orders;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Price
 */
class GrandTotalPurchased extends Column
{
    protected $_customerSession;
    protected $_resource;
	protected $_mkCoreOrder;
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceFormatter;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PriceCurrencyInterface $priceFormatter
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\ResourceConnection $resource,
		\Magento\Sales\Model\OrderFactory $mkCoreOrder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceCurrencyInterface $priceFormatter,
        array $components = [],
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_resource = $resource;
		$this->_mkCoreOrder = $mkCoreOrder;
        $this->priceFormatter = $priceFormatter;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();
        $tableSalelist = $this->_resource->getTableName('multivendor_saleslist');
        
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $coreOrderModel = $this->_mkCoreOrder->create();
        		$orders = $coreOrderModel->getCollection();
                $orders->getSelect()->joinLeft(
                    array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid AND main_table.entity_id = "'.$item['entity_id'].'"',
                    array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
                );
        		$orders->getSelect()->where('mk_sales_list.sellerid=?',$sellerid);
        		$orders->getSelect()->group('main_table.entity_id');
                $data = $orders->getFirstItem();
                $item['grand_total'] = $data->getTotalamount();
                                                
                $currencyCode = isset($item['order_currency_code']) ? $item['order_currency_code'] : null;
                $item[$this->getData('name')] =
                    $this->priceFormatter->format(
                        $item[$this->getData('name')],
                        false,
                        null,
                        null,
                        $currencyCode
                    );                    
            }
        }
        return $dataSource;
    }
}
