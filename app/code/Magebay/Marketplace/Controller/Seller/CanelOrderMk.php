<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Seller;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory; 
class CanelOrderMk extends \Magento\Framework\App\Action\Action{

	protected $_resultJsonFactory;
    //protected $_objectmanager;
    protected $_mkCoreOrder;
    protected $shipmentLoader;
	
	public function __construct(	
		Context $context,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        //\Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Sales\Model\OrderFactory $mkCoreOrder,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader
	){
		parent::__construct($context);	
		$this->_resultJsonFactory = $resultJsonFactory;
        //$this->_objectmanager = $objectmanager;  
        $this->_mkCoreOrder = $mkCoreOrder;   
        $this->shipmentLoader = $shipmentLoader;    
	}

	public function execute(){		
		$data = $this->getRequest()->getPost();
        $sellerId = $data['sellerid']; 
        $oldUrl = $data['old_url'];
        $orderId = $data['order_id'];
        $items_cancel = $data['items'];
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);    
        $vai = 0;
        foreach ($order->getAllVisibleItems() as $item) {
            if($items_cancel[$item->getId()] > 0){
                $vai ++;
            }
        }
        //if order only have item of this seller set cancel order
        if($vai == count($order->getAllVisibleItems())){
            if($order->canCancel()){
                $order->cancel();
                $order->save();
            }
        }else{ //if order have than one seller set cancel item of this seller
            foreach ($order->getAllVisibleItems() as $item) {
                $item->setQtyCanceled($items_cancel[$item->getId()]);
                $item->save();
            }
        }
        $this->messageManager->addSuccess(__('The order has been canceled.'));
		$this->_redirect($oldUrl);        
	}
}