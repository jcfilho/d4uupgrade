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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\InvoiceService;
class SaveInvoiceMk extends \Magento\Framework\App\Action\Action{

	protected $_resultJsonFactory;
    //protected $_objectmanager;
    protected $_mkCoreOrder;
    protected $invoiceSender;
    protected $shipmentSender;
    protected $shipmentFactory;
    protected $registry;
    private $invoiceService;
	
	public function __construct(	
		Context $context,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        //\Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Sales\Model\OrderFactory $mkCoreOrder,
        Registry $registry,
        InvoiceSender $invoiceSender,
        ShipmentSender $shipmentSender,
        ShipmentFactory $shipmentFactory,
        InvoiceService $invoiceService
	){
		parent::__construct($context);	
		$this->_resultJsonFactory = $resultJsonFactory;
        //$this->_objectmanager = $objectmanager;  
        $this->_mkCoreOrder = $mkCoreOrder;   
        $this->registry = $registry;
        $this->invoiceSender = $invoiceSender;
        $this->shipmentSender = $shipmentSender;
        $this->shipmentFactory = $shipmentFactory;
        $this->invoiceService = $invoiceService;
	}
        
    /**
     * Prepare shipment
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return \Magento\Sales\Model\Order\Shipment|false
     */
    protected function _prepareShipment($invoice)
    {
        $invoiceData = $this->getRequest()->getParam('invoice');

        $shipment = $this->shipmentFactory->create(
            $invoice->getOrder(),
            isset($invoiceData['items']) ? $invoiceData['items'] : [],
            $this->getRequest()->getPost('tracking')
        );

        if (!$shipment->getTotalQty()) {
            return false;
        }

        return $shipment->register();
    }
    /**
     * Save invoice
     * We can save only new invoice. Existing invoices are not editable
     *
     * @return \Magento\Framework\Controller\ResultInterface
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();
        $sellerId = $data['sellerid']; 
        $oldUrl = $data['old_url'];
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPost('invoice');
        $orderId = $this->getRequest()->getPost('order_id');
        if (!empty($data['comment_text'])) {
            $this->_objectManager->get('Magento\Backend\Model\Session')->setCommentText($data['comment_text']);
        }
        try {
            $invoiceData = $this->getRequest()->getPost('invoice', []);
            $invoiceItems = isset($invoiceData['items']) ? $invoiceData['items'] : [];
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
            if (!$order->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('The order no longer exists.'));
            }
            if (!$order->canInvoice()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The order does not allow an invoice to be created.')
                );
            }

            $invoice = $this->invoiceService->prepareInvoice($order, $invoiceItems);
            if (!$invoice) {
                throw new LocalizedException(__('We can\'t save the invoice right now.'));
            }
            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You can\'t create an invoice without products.')
                );
            }
            $this->registry->register('current_invoice', $invoice);
            
            if (!empty($data['capture_case'])) {
                $invoice->setRequestedCaptureCase($data['capture_case']);
            }
            if (!empty($data['comment_text'])) {
                $invoice->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                );
                $invoice->setCustomerNote($data['comment_text']);
                $invoice->setCustomerNoteNotify(isset($data['comment_customer_notify']));
            }

            $invoice->register();
            $invoice->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
            $invoice->getOrder()->setIsInProcess(true);
            $transactionSave = $this->_objectManager->create(
                'Magento\Framework\DB\Transaction'
            )->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );
            
            $shipment = false;
            if (!empty($data['do_shipment']) || (int)$invoice->getOrder()->getForcedShipmentWithInvoice()) {
                $shipment = $this->_prepareShipment($invoice);
                if ($shipment) {
                    $transactionSave->addObject($shipment);
                }
            }
            $transactionSave->save();

            if (isset($shippingResponse) && $shippingResponse->hasErrors()) {
                $this->messageManager->addError(
                    __(
                        'The invoice and the shipment  have been created. ' .
                        'The shipping label cannot be created now.'
                    )
                );
            } elseif (!empty($data['do_shipment'])) {
                $this->messageManager->addSuccess(__('You created the invoice and shipment.'));
            } else {
                $this->messageManager->addSuccess(__('The invoice has been created.'));
            }

            // send invoice/shipment emails
            try {
                if (!empty($data['send_email'])) {
                    $this->invoiceSender->send($invoice);
                }
            } catch (\Exception $e) {
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->messageManager->addError(__('We can\'t send the invoice email right now.'));
            }
            if ($shipment) {
                try {
                    if (!empty($data['send_email'])) {
                        $this->shipmentSender->send($shipment);
                    }
                } catch (\Exception $e) {
                    $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                    $this->messageManager->addError(__('We can\'t send the shipment right now.'));
                }
            }
            $this->_objectManager->get('Magento\Backend\Model\Session')->getCommentText(true);
            $this->_redirect($oldUrl); 
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t save the invoice right now.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }
        $this->_redirect($oldUrl);  
    }
}