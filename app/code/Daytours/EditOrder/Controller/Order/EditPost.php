<?php

namespace Daytours\EditOrder\Controller\Order;

use Daytours\EditOrder\Controller\Order;
use Daytours\EditOrder\Model\Order\Order as OrderModel;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\AbstractController\OrderViewAuthorization;
use Magento\Store\Model\StoreManagerInterface;
use \Magento\Sales\Api\OrderCustomerManagementInterface;

class EditPost extends Order
{

    /**
     * @var CustomerFactory
     */
    private $customer;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var OrderCustomerManagementInterface
     */
    private $orderCustomerService;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        OrderViewAuthorization $orderAuthorization,
        OrderRepositoryInterface $orderFactory,
        Registry $registry,
        OrderModel $order,
        Validator $formKeyValidator,
        CustomerFactory $customer,
        StoreManagerInterface $storeManager,
        OrderCustomerManagementInterface $orderCustomerService
    )
    {
        parent::__construct($context, $pageFactory, $orderAuthorization, $orderFactory, $registry, $order, $formKeyValidator);
        $this->customer = $customer;
        $this->storeManager = $storeManager;
        $this->orderCustomerService = $orderCustomerService;
    }

    public function execute()
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');
        if (
            $this->formKeyValidator->validate($this->getRequest())
            && $orderId
        ) {
            $order = $this->orderFactory->get($orderId);
            //if ($this->orderAuthorization->canView($order)) {
            $this->order->editOptions($order, $this->getRequest()->getParams());

            /** @var  $customer \Magento\Customer\Model\Customer */
            $websiteInOrder = $this->storeManager->getStore($order->getStoreId())->getWebsiteId();
            $customer = $this->customer->create();
            $customer->setWebsiteId($websiteInOrder);
            $customer->loadByEmail($order->getCustomerEmail());

//            $customerEmailOrder = $order->getCustomerEmail();
//            $customer = $this->customer->create()->loadByEmail($customerEmailOrder);

            if( $customer->getId() ){
                $this->messageManager->addSuccess(__('Order edited successfully.'));
                return $this->_redirect('sales/order/view/order_id/' . $orderId);
            }else{

                $message = __('Your information was updated successfully.');
                try {
                    if( $this->getRequest()->getParam('checkbox_register_customer') ){
                        $this->orderCustomerService->create($orderId);
                        $message .= "<br/>" . __('A letter with further instructions will be sent to your email.');
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, $e->getMessage());
//                    throw $e;
                }
                $this->messageManager->addSuccess($message);
                return $this->_redirect('/');
            }


            //}
        }

        return $this->_redirect('sales/order/history');
    }
}