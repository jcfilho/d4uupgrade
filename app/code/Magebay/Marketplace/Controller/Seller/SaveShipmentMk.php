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
class SaveShipmentMk extends \Magento\Framework\App\Action\Action{

	protected $_resultJsonFactory;
    //protected $_objectmanager;
    protected $_mkCoreOrder;
    protected $shipmentLoader;
    /**
     * @var \Magento\Shipping\Model\Shipping\LabelGenerator
     */
    protected $labelGenerator;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentSender
     */
    protected $shipmentSender;
	
	public function __construct(	
		Context $context,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        //\Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Sales\Model\OrderFactory $mkCoreOrder,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
        \Magento\Shipping\Model\Shipping\LabelGenerator $labelGenerator,
        \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender
	){
		parent::__construct($context);	
		$this->_resultJsonFactory = $resultJsonFactory;
        //$this->_objectmanager = $objectmanager;  
        $this->_mkCoreOrder = $mkCoreOrder;   
        $this->shipmentLoader = $shipmentLoader;   
        $this->labelGenerator = $labelGenerator;
        $this->shipmentSender = $shipmentSender; 
	}
    
    protected function _getItemQtys()
    {
        $data = $this->getRequest()->getPost('shipment');
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = array();
        }
        return $qtys;
    }
    
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transaction = $this->_objectManager->create(
            'Magento\Framework\DB\Transaction'
        );
        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->save();

        return $this;
    }
	
	public function execute(){		
		$data = $this->getRequest()->getPost();
        $sellerId = $data['sellerid']; 
        $oldUrl = $data['old_url'];
        //Save shippment
        $order = $this->_mkCoreOrder->create()->load($data['order_id']);
        if (!$order->canShip()) {
            $this->messageManager->addError(__('We can\'t save the shipment right now.'));
        }else{
            $data = $this->getRequest()->getPost('shipment');
    
            if (!empty($data['comment_text'])) {
                $this->_objectManager->get('Magento\Backend\Model\Session')->setCommentText($data['comment_text']);
            }
            
            $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];
            
            try {
                $this->shipmentLoader->setOrderId($this->getRequest()->getPost('order_id'));
                $this->shipmentLoader->setShipmentId($this->getRequest()->getPost('shipment_id'));
                $this->shipmentLoader->setShipment($data);
                $this->shipmentLoader->setTracking($this->getRequest()->getPost('tracking'));
                $shipment = $this->shipmentLoader->load();
                if (!$shipment) {
                    $this->messageManager->addError(__('Cannot save shipment.'));
                }
                
                if (!empty($data['comment_text'])) {
                    $shipment->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );
    
                    $shipment->setCustomerNote($data['comment_text']);
                    $shipment->setCustomerNoteNotify(isset($data['comment_customer_notify']));
                }
    
                $shipment->register();
                            
                $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $responseAjax = new \Magento\Framework\DataObject();
    
                if ($isNeedCreateLabel) {
                    $this->labelGenerator->create($shipment, $this->_request);
                    $responseAjax->setOk(true);
                }
    
                $this->_saveShipment($shipment);
                
                if (!empty($data['send_email'])) {
                    $this->shipmentSender->send($shipment);
                }
                
                $shipmentCreatedMessage = __('The shipment has been created.');
                $labelCreatedMessage = __('You created the shipping label.');
    
                $this->messageManager->addSuccess(
                    $isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage : $shipmentCreatedMessage
                );
                $this->_objectManager->get('Magento\Backend\Model\Session')->getCommentText(true);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
		$this->_redirect($oldUrl);        
	}
}