<?php
 
namespace Magebay\Bookingsystem\Block\Adminhtml\Bookingorders;

use Magento\Backend\Block\Widget\Grid as WidgetGrid;
use Magento\Backend\Helper\Data as BackendHelper;
class Grid extends WidgetGrid
{
	protected $_priceHelper;
	 /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
	 
    protected $_localeCurrency;
	/**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Backend\Helper\Data $backendHelper,
		\Magento\Framework\App\ResourceConnection $resource,
		array $data = []
	) 
	{
		$this->_resource = $resource;
		parent::__construct($context, $backendHelper, $data);
	}
    protected function _prepareCollection()
	{
		$storeId = $this->_request->getParam('store',0);
		if ($this->getCollection()) {
			$collection = $this->getCollection();
			$tableBookingOroder = $this->_resource->getTableName('booking_orders');
			$collection->getSelect()->joinLeft(array('bk_order'=>$tableBookingOroder),'main_table.entity_id = bk_order.bkorder_order_id',array('bkorder_customer'));
			$collection->getSelect()->where("bk_order.bkorder_id IS NOT NULL");
			if($storeId > 0)
			{
				$collection->addFieldToFilter('store_id',$storeId);
			}
			$collection->getSelect()->group('main_table.entity_id');
			$this->setCollection($collection); 
            $this->_preparePage();
            $columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
            $dir = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
            $filter = $this->getParam($this->getVarNameFilter(), null);
            if (is_null($filter)) {
                $filter = $this->_defaultFilter;
            }
            if (is_string($filter)) {
                $data = $this->_backendHelper->prepareFilterString($filter);
                $data = array_merge($data, (array)$this->getRequest()->getPost($this->getVarNameFilter()));
				if(isset($data['bkorder_customer']))
				{
					$this->getCollection()->getSelect()->where("bk_order.bkorder_customer LIKE'%{$data['bkorder_customer']}%'");
					unset($data['bkorder_customer']);
				}
                $this->_setFilterValues($data);
            } elseif ($filter && is_array($filter)) {
                $this->_setFilterValues($filter);
            } elseif (0 !== sizeof($this->_defaultFilter)) {
                $this->_setFilterValues($this->_defaultFilter);
            }
            if ($this->getColumn($columnId) && $this->getColumn($columnId)->getIndex()) {
                $dir = strtolower($dir) == 'desc' ? 'desc' : 'asc';
                $this->getColumn($columnId)->setDir($dir);
                $this->_setCollectionOrder($this->getColumn($columnId));
            }
        }
        return $this;
 
	}
	
}