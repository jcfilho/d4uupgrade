<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Helper;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer;

class EmailSeller extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_TRANS_EMAIL_GENERAL_EMAIL = 'trans_email/ident_general/email';
    const XML_PATH_TRANS_EMAIL_GENERAL_NAME = 'trans_email/ident_general/name';

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\GiftMessage\Model\MessageFactory
     */
    protected $_giftMessageFactory;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var Renderer
     */
    protected $_addressRenderer;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentHelperData;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context              $context
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\Mail\Template\TransportBuilder  $transportBuilder
     * @param Renderer                                           $addressRenderer
     * @param \Magento\Payment\Helper\Data                       $paymentHelperData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\ObjectManagerInterface          $objectManager
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\GiftMessage\Model\MessageFactory          $giftMessageFactory
     * @param \Magento\Checkout\Model\Session                    $checkoutSession
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface  $priceCurrency
     * @param \Magento\Newsletter\Model\SubscriberFactory        $subscriberFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        Renderer $addressRenderer,
        \Magento\Payment\Helper\Data $paymentHelperData,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\GiftMessage\Model\MessageFactory $giftMessageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
    )
    {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->_paymentHelperData = $paymentHelperData;
        $this->_addressRenderer = $addressRenderer;
        $this->_objectManager = $objectManager;
        $this->_giftMessageFactory = $giftMessageFactory;
        $this->inlineTranslation = $inlineTranslation;
        $this->_checkoutSession = $checkoutSession;
        $this->_priceCurrency = $priceCurrency;
    }

    /**
     * @param $email
     */
    public function addSubscriber($email)
    {
        if ($email) {
            $subscriberModel = $this->_subscriberFactory->create()->loadByEmail($email);
            if ($subscriberModel->getId() === NULL) {
                try {
                    $this->_subscriberFactory->create()->subscribe($email);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {

                } catch (\Exception $e) {

                }
            } elseif ($subscriberModel->getData('subscriber_status') != 1) {
                $subscriberModel->setData('subscriber_status', 1);
                try {
                    $subscriberModel->save();
                } catch (\Exception $e) {

                }
            }
        }
    }

    /**
     * Get payment info block as html
     *
     * @param Order $order
     *
     * @return string
     */
    protected function getPaymentHtml(Order $order, $storeId)
    {
        return $this->_paymentHelperData->getInfoBlockHtml(
            $order->getPayment(),
            $storeId
        );
    }

    /**
     * @return \Magento\Checkout\Model\Type\Onepage
     */
    public function getOnePage()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Type\Onepage');
    }

    /**
     * @param Order $order
     *
     * @return string|null
     */
    protected function getFormattedShippingAddress($order)
    {
        return $order->getIsVirtual()
            ? NULL
            : $this->_addressRenderer->format($order->getShippingAddress(), 'html');
    }

    /**
     * @param Order $order
     *
     * @return string|null
     */
    protected function getFormattedBillingAddress($order)
    {
        return $this->_addressRenderer->format($order->getBillingAddress(), 'html');
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function sendNewOrderEmail(\Magento\Sales\Model\Order $order,$sellerId)
    {
        $storeId = $order->getStore()->getId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$customer = $objectManager->create('Magento\Customer\Model\Customer')->load($sellerId);
        $this->inlineTranslation->suspend();
        try {
            $transport = $this->_transportBuilder->setTemplateIdentifier(
                'marketplace_general_email_order_vendor'
            )->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
            )->setTemplateVars(
                [
                    'seller_id'                => $sellerId,
                    'seller_name'              => $customer->getData('firstname') . ' ' . $customer->getData('lastname'),
                    'order'                    => $order,
                    'billing'                  => $order->getBillingAddress(),
                    'payment_html'             => $this->getPaymentHtml($order, $storeId),
                    'store'                    => $order->getStore(),
                    'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                    'formattedBillingAddress'  => $this->getFormattedBillingAddress($order),
                ]
            )->setFrom(
                [
                    'email' => \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\Marketplace\Helper\Data')->getAdminEmail(),
                    'name'  => 'Admin Notification',
                ]
            )->addTo(
                [
                    'email' => $customer->getData('email'),
                    'name'  => $customer->getData('firstname') . ' ' . $customer->getData('lastname')
                ]
            )->getTransport();
            $transport->sendMessage();
        } catch (\Magento\Framework\Exception\MailException $ex) {

        }
        $this->inlineTranslation->resume();
    }
    
    /**
     * @param \Magento\Customer\Model\Customer $customer
     */
    public function sendRegisterSellerEmail($customer)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $this->inlineTranslation->suspend();
        try {
            $transport = $this->_transportBuilder->setTemplateIdentifier(
                'marketplace_general_email_register_vendor'
            )->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
            )->setTemplateVars(
                [
                    'myvar1' => 'Admin',
                    'myvar2' => \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Backend\Helper\Data')->getUrl('customer/index/edit', array('id'=>$customer[0]['entity_id']))
                ]
            )->setFrom(
                [
                    'email' => $customer[0]['email'],
                    'name'  => $customer[0]['firstname'].$customer[0]['lastname']
                ]
            )->addTo(
                [
                    'email' => \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\Marketplace\Helper\Data')->getAdminEmail(),
                    'name'  => 'Admin'
                ]
            )->getTransport();
            $transport->sendMessage();
        } catch (\Magento\Framework\Exception\MailException $ex) {

        }
        $this->inlineTranslation->resume();
    }
    
    /**
     * @param id customer
     */
    public function sendApproveSellerEmail($customer_id)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$customer = $objectManager->create('Magento\Customer\Model\Customer')->load($customer_id);
        $this->inlineTranslation->suspend();
        try {
            $transport = $this->_transportBuilder->setTemplateIdentifier(
                'marketplace_general_email_approve_vendor'
            )->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
            )->setTemplateVars(
                [
                    'myvar1' => $customer->getData('firstname') . ' ' . $customer->getData('lastname'),
                    'myvar2' => $this->_storeManager->getStore()->getUrl('customer/account/login')
                ]
            )->setFrom(
                [
                    'email' => \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\Marketplace\Helper\Data')->getAdminEmail(),
                    'name'  => 'Admin Notification'
                ]
            )->addTo(
                [
                    'email' => $customer->getData('email'),
                    'name'  => $customer->getData('firstname') . ' ' . $customer->getData('lastname')
                ]
            )->getTransport();
            $transport->sendMessage();
        } catch (\Magento\Framework\Exception\MailException $ex) {

        }
        $this->inlineTranslation->resume();
    }
    
    /**
     * @param id customer
     */
    public function sendUnapproveSellerEmail($customer_id)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$customer = $objectManager->create('Magento\Customer\Model\Customer')->load($customer_id);
        $this->inlineTranslation->suspend();
        try {
            $transport = $this->_transportBuilder->setTemplateIdentifier(
                'marketplace_general_email_unapprove_vendor'
            )->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
            )->setTemplateVars(
                [
                    'myvar1' => $customer->getData('firstname') . ' ' . $customer->getData('lastname'),
                    'myvar2' => $this->_storeManager->getStore()->getUrl('customer/account/login')
                ]
            )->setFrom(
                [
                    'email' => \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\Marketplace\Helper\Data')->getAdminEmail(),
                    'name'  => 'Admin Notification'
                ]
            )->addTo(
                [
                    'email' => $customer->getData('email'),
                    'name'  => $customer->getData('firstname') . ' ' . $customer->getData('lastname')
                ]
            )->getTransport();
            $transport->sendMessage();
        } catch (\Magento\Framework\Exception\MailException $ex) {

        }
        $this->inlineTranslation->resume();
    }
    
    public function sendRequestWithdrawEmailToSeller($data)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$customer = $objectManager->create('Magento\Customer\Model\Customer')->load($data['seller_id']);
        //data 
        $paymentDetail = $objectManager->create('Magebay\Marketplace\Model\Payments')->load($data['payment_id']);                
        $this->inlineTranslation->suspend();
        try {
            $transport = $this->_transportBuilder->setTemplateIdentifier(
                'marketplace_general_email_withdraw_vendor'
            )->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
            )->setTemplateVars(
                [
                    'payment' => $paymentDetail,    
                    'payment_email' => $data['payment_email'],   
                    'amount' => \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Pricing\Helper\Data')->currency($data['transaction_amount'],true,false),    
                    'fee' => \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Pricing\Helper\Data')->currency($data['amount_fee'],true,false),          
                    'net' => \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Pricing\Helper\Data')->currency($data['amount_paid'],true,false),  
                    'info' => $data['payment_additional'],          
                    'myvar1' => $customer->getData('firstname') . ' ' . $customer->getData('lastname')
                ]
            )->setFrom(
                [
                    'email' => \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\Marketplace\Helper\Data')->getAdminEmail(),
                    'name'  => 'Admin Notification'
                ]
            )->addTo(
                [
                    'email' => $customer->getData('email'),
                    'name'  => $customer->getData('firstname') . ' ' . $customer->getData('lastname')
                ]
            )->getTransport();
            $transport->sendMessage();
        } catch (\Magento\Framework\Exception\MailException $ex) {

        }
        $this->inlineTranslation->resume();
    }
    
    public function sendRequestWithdrawEmailToAdmin($data)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$customer = $objectManager->create('Magento\Customer\Model\Customer')->load($data['seller_id']);
        //data 
        $paymentDetail = $objectManager->create('Magebay\Marketplace\Model\Payments')->load($data['payment_id']);                
        $this->inlineTranslation->suspend();
        try {
            $transport = $this->_transportBuilder->setTemplateIdentifier(
                'marketplace_general_email_withdraw_admin'
            )->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
            )->setTemplateVars(
                [
                    'seller_name' => $customer->getData('firstname') . ' ' . $customer->getData('lastname'),
                    'payment' => $paymentDetail,    
                    'payment_email' => $data['payment_email'],   
                    'amount' => \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Pricing\Helper\Data')->currency($data['transaction_amount'],true,false),    
                    'fee' => \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Pricing\Helper\Data')->currency($data['amount_fee'],true,false),          
                    'net' => \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Pricing\Helper\Data')->currency($data['amount_paid'],true,false),   
                    'info' => $data['payment_additional'],          
                    'myvar1' => 'Admin'
                ]
            )->setFrom(
                [
                    'email' => $customer->getData('email'),
                    'name'  => $customer->getData('firstname') . ' ' . $customer->getData('lastname')
                ]
            )->addTo(
                [
                    'email' => \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\Marketplace\Helper\Data')->getAdminEmail(),
                    'name'  => 'Admin'
                ]
            )->getTransport();
            $transport->sendMessage();
        } catch (\Magento\Framework\Exception\MailException $ex) {

        }
        $this->inlineTranslation->resume();
    }
    
    public function sendCompleteWithdrawEmailToSeller($data)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$customer = $objectManager->create('Magento\Customer\Model\Customer')->load($data['seller_id']);
        //data 
        $paymentDetail = $objectManager->create('Magebay\Marketplace\Model\Payments')->load($data['payment_id']);                
        $this->inlineTranslation->suspend();
        try {
            $transport = $this->_transportBuilder->setTemplateIdentifier(
                'marketplace_general_email_complete_withdraw_vendor'
            )->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
            )->setTemplateVars(
                [
                    'payment' => $paymentDetail,    
                    'payment_email' => $data['payment_email'],   
                    'amount' => \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Pricing\Helper\Data')->currency($data['transaction_amount'],true,false),    
                    'fee' => \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Pricing\Helper\Data')->currency($data['amount_fee'],true,false),          
                    'net' => \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Pricing\Helper\Data')->currency($data['amount_paid'],true,false),  
                    'info' => $data['payment_additional'], 
                    'admin_comnent' => $data['note'],     
                    'status' => $data['status'],       
                    'myvar1' => $customer->getData('firstname') . ' ' . $customer->getData('lastname')
                ]
            )->setFrom(
                [
                    'email' => \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\Marketplace\Helper\Data')->getAdminEmail(),
                    'name'  => 'Admin Notification'
                ]
            )->addTo(
                [
                    'email' => $customer->getData('email'),
                    'name'  => $customer->getData('firstname') . ' ' . $customer->getData('lastname')
                ]
            )->getTransport();
            $transport->sendMessage();
        } catch (\Magento\Framework\Exception\MailException $ex) {

        }
        $this->inlineTranslation->resume();
    }
    
    public function sendCompelteWithdrawEmailToAdmin($data)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$customer = $objectManager->create('Magento\Customer\Model\Customer')->load($data['seller_id']);
        //data 
        $paymentDetail = $objectManager->create('Magebay\Marketplace\Model\Payments')->load($data['payment_id']);                
        $this->inlineTranslation->suspend();
        try {
            $transport = $this->_transportBuilder->setTemplateIdentifier(
                'marketplace_general_email_complete_withdraw_admin'
            )->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
            )->setTemplateVars(
                [
                    'seller_name' => $customer->getData('firstname') . ' ' . $customer->getData('lastname'),
                    'payment' => $paymentDetail,    
                    'payment_email' => $data['payment_email'],   
                    'amount' => \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Pricing\Helper\Data')->currency($data['transaction_amount'],true,false),    
                    'fee' => \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Pricing\Helper\Data')->currency($data['amount_fee'],true,false),          
                    'net' => \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Pricing\Helper\Data')->currency($data['amount_paid'],true,false),   
                    'info' => $data['payment_additional'],   
                    'admin_comnent' => $data['note'],   
                    'status' => $data['status'],        
                    'myvar1' => 'Admin'
                ]
            )->setFrom(
                [
                    'email' => $customer->getData('email'),
                    'name'  => $customer->getData('firstname') . ' ' . $customer->getData('lastname')
                ]
            )->addTo(
                [
                    'email' => \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\Marketplace\Helper\Data')->getAdminEmail(),
                    'name'  => 'Admin'
                ]
            )->getTransport();
            $transport->sendMessage();
        } catch (\Magento\Framework\Exception\MailException $ex) {

        }
        $this->inlineTranslation->resume();
    }
    
    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if (empty($this->_quote)) {
            $this->_quote = $this->_checkoutSession->getQuote();
        }

        return $this->_quote;
    }
}