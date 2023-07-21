<?php
namespace Magebay\Marketplace\Ui\Component\Listing\DataProviders\Seller\Todo;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class Orders extends \Magento\Ui\DataProvider\AbstractDataProvider
{    
	protected $_resource;
	protected $_order;
	protected $_modelSession;
	protected $_summaryFactory;
	
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        //\Magebay\Marketplace\Model\ResourceModel\TodoItem\CollectionFactory $collectionFactory,
		//\Magento\Catalog\Model\ProductFactory $collectionFactory,
		//\Magento\Customer\Model\CustomerFactory $collectionFactory,
		//\Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Framework\Url\Helper\Data $urlHelper,
		\Magento\Framework\App\ResourceConnection $resource,
		//\Magento\Sales\Model\OrderFactory $order,
		//\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $order,
		\Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory $order,
		//\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $order,
		\Magento\Customer\Model\Session $modelSession,
        array $meta = [],
        array $data = []
    ) {
		$this->_resource = $resource;
		$this->_order = $order;
		$this->_modelSession = $modelSession;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
		//$this->collection = $order->create()->addAttributeToSelect('*');
		$this->collection = $order->create();
    }
	
	public function getData()
    {
		$collection = $this->collection;
		$seller = $this->_modelSession;
		$tableSalelist = $this->_resource->getTableName('multivendor_saleslist');
		$collection->getSelect()->joinLeft(
            array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
            array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
        );
		$collection->getSelect()->where('mk_sales_list.sellerid=?', $seller->getId() );
		$collection->getSelect()->group('main_table.entity_id');
		if (!$collection->isLoaded()) {
			$collection->load();
		}
        $items = $collection->toArray();
        return [
            'totalRecords' => $collection->getSize(),
            //'items' => array_values($items),
            'items' => array_values( $collection->getData() ),
        ];
    }	
}
