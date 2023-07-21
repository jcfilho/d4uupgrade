<?php

namespace Daytours\EditOrder\Controller\Order;

use Daytours\EditOrder\Controller\Order;
use Daytours\EditOrder\Model\Order\Order as OrderModel;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\AbstractController\OrderViewAuthorization;
use Daytours\EditOrder\Helper\Data as DataEditOrder;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\ResourceModel\CustomerRepositoryFactory;
use Magento\Store\Model\StoreManagerInterface;

class Edit extends Order
{

    /**
     * @var DataEditOrder
     */
    private $dataEditOrder;
    /**
     * @var CustomerFactory
     */
    private $customer;
    /**
     * @var Session
     */
    private $session;
    /**
     * @var CustomerRepositoryFactory
     */
    private $customerRepositoryFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        OrderViewAuthorization $orderAuthorization,
        OrderRepositoryInterface $orderFactory,
        Registry $registry,
        OrderModel $order,
        Validator $formKeyValidator,
        DataEditOrder $dataEditOrder,
        CustomerFactory $customer,
        Session $session,
        CustomerRepositoryFactory $customerRepositoryFactory,
        StoreManagerInterface $storeManager
    )
    {
        parent::__construct($context, $pageFactory, $orderAuthorization, $orderFactory, $registry, $order, $formKeyValidator);

        $this->dataEditOrder = $dataEditOrder;
        $this->customer = $customer;
        $this->session = $session;
        $this->customerRepositoryFactory = $customerRepositoryFactory;
        $this->storeManager = $storeManager;
    }

    private function _renderPageWithFormToCustomer($order,$orderId,$orderCustomerEmail){
        //Order with customer
        if ($orderId) {
//            $order = $this->orderFactory->get($orderId);
            if( $order->getPostVenta() == 0 && $orderCustomerEmail == $order->getCustomerEmail() ){
                $this->registry->register('current_order', $order);
                return $this->_pageFactory->create();
            }
            else{
                $this->messageManager->addError(__('The order %1 was completed, you can not edit the information again.', $order->getIncrementId()));
                return $this->_redirect('sales/order/history');
            }
        }
        return $this->_redirect('sales/order/history');
    }

    private function _renderPageWithFormWithOutCustomer($order,$orderId,$orderCustomerEmail){
        //Order withOut customer
        if ($orderId) {
            if( $order->getPostVenta() == 0 && $orderCustomerEmail == $order->getCustomerEmail() ){
                $this->registry->register('current_order', $order);
                return $this->_pageFactory->create();
            }
            else{
                $this->messageManager->addError(__('The order %1 was completed, you can not edit the information again.', $order->getIncrementId()));
                return $this->_redirect('/');
            }
        }
        return $this->_redirect('/');
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $dataUrl = $this->getRequest()->getParam('data');
        $data = $this->dataEditOrder->getDataURLDecrypted($dataUrl);
        if( count($data) == 2 ){
            $orderId = $data[0];
            $orderCustomerEmail = $data[1];
            try {

                $order = $this->orderFactory->get($orderId);

                /** @var  $customer \Magento\Customer\Model\Customer */
                $websiteInOrder = $this->storeManager->getStore($order->getStoreId())->getWebsiteId();
                $customer = $this->customer->create();
                $customer->setWebsiteId($websiteInOrder);
                $customer->loadByEmail($orderCustomerEmail);

                if( $customer->getId() ){
                    /** @var  $customer \Magento\Customer\Model\Customer */
                    if( $this->session->setCustomerAsLoggedIn($customer) ){
                        return $this->_renderPageWithFormToCustomer($order,$orderId,$orderCustomerEmail);
                    }else{
                        $this->messageManager->addError(__('Occurred a problem, customer not logged please try again.'));
                        return $this->_redirect('/');
                    }
                }else{
                    return $this->_renderPageWithFormWithOutCustomer($order,$orderId,$orderCustomerEmail);
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addError(__('Occurred a problem, please contact with support.'));
                return $this->_redirect('/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError(__('Occurred a problem, please contact with support.'));
                return $this->_redirect('/');
            }
        }
        return $this->_redirect('/');
    }
}