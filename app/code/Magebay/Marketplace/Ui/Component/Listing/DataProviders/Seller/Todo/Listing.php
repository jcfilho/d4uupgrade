<?php
namespace Magebay\Marketplace\Ui\Component\Listing\DataProviders\Seller\Todo;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class Listing extends \Magento\Ui\DataProvider\AbstractDataProvider
{    
	protected $_resource;
	protected $_product;
	protected $_modelSession;
	protected $_summaryFactory;
	
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        //\Magebay\Marketplace\Model\ResourceModel\TodoItem\CollectionFactory $collectionFactory,
		//\Magento\Catalog\Model\ProductFactory $collectionFactory,
		//\Magento\Customer\Model\CustomerFactory $collectionFactory,
		\Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Catalog\Model\Product $product,
		\Magento\Customer\Model\Session $modelSession,
		\Magento\Review\Model\Review\SummaryFactory $summaryFactory,
		CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
		$this->_resource = $resource;
		$this->_product = $product;
		$this->_modelSession = $modelSession;
		$this->_summaryFactory = $summaryFactory;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
		$this->collection = $collectionFactory->create();
    }
	
	public function getData()
    {
		$collection = $this->collection;
		$seller = $this->_modelSession;
        if($seller && $seller->getId() ){
			$customerSession = $this->_modelSession;
			$tableMKproduct = $this->_resource->getTableName('multivendor_product');
			$collection = $this->collection;
			$collection->addAttributeToSelect(array('*'));
			if($customerSession->isLoggedIn()){
				
			}else{
				$collection->addAttributeToFilter('status',1);
			}
			$collection->getSelect()->joinLeft(array('mk_product'=>$tableMKproduct),'e.entity_id = mk_product.product_id',array('mkproductstatus'=>"mk_product.status"))->where('mk_product.user_id=?',$seller->getId());
            $collection->addAttributeToSort('entity_id', 'DESC');
		}
		
		if (!$collection->isLoaded()) {
			$collection->load();
		}
        $items = $collection->toArray();
        return [
            'totalRecords' => $collection->getSize(),
            'items' => array_values($items),
        ];
    }
}
