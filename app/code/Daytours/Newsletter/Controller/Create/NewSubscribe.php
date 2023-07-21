<?php

namespace Daytours\Newsletter\Controller\Create;
use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory as JsonFactoryAlias;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\StoreManagerInterface;

class NewSubscribe extends \Magento\Newsletter\Controller\Subscriber
{

    /**
     * @var JsonFactoryAlias
     */
    private $resultJsonFactory;
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var CustomerUrl
     */
    private $customerUrl;
    /**
     * @var CustomerAccountManagement
     */
    private $customerAccountManagement;

    public function __construct(
        Context $context,
        SubscriberFactory $subscriberFactory,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        CustomerUrl $customerUrl,
        CustomerAccountManagement $customerAccountManagement,
        JsonFactoryAlias $resultJsonFactory
    )
    {
        parent::__construct($context, $subscriberFactory, $customerSession, $storeManager, $customerUrl);
        $this->subscriberFactory = $subscriberFactory;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->customerUrl = $customerUrl;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerAccountManagement = $customerAccountManagement;
    }

    /**
     * Validates that the email address isn't being used by a different account.
     *
     * @param string $email
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function validateEmailAvailable($email)
    {
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        if ($this->_customerSession->getCustomerDataObject()->getEmail() !== $email
            && !$this->customerAccountManagement->isEmailAvailable($email, $websiteId)
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('This email address is already assigned to another user.')
            );
        }
    }

    /**
     * Validates that if the current user is a guest, that they can subscribe to a newsletter.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function validateGuestSubscription()
    {
        if ($this->_objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class)
                ->getValue(
                    \Magento\Newsletter\Model\Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ) != 1
            && !$this->_customerSession->isLoggedIn()
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Sorry, but the administrator denied subscription for guests. Please <a href="%1">register</a>.',
                    $this->_customerUrl->getRegisterUrl()
                )
            );
        }
    }

    /**
     * Validates the format of the email address
     *
     * @param string $email
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function validateEmailFormat($email)
    {
        if (!\Zend_Validate::is($email, \Magento\Framework\Validator\EmailAddress::class)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a valid email address.'));
        }
    }

    public function execute()
    {
        $response = [];

        if ($this->getRequest()->isAjax() && $this->getRequest()->getParam('emailNewsleter')) {
            $email = (string)$this->getRequest()->getParam('emailNewsleter');

            try {
                $this->validateEmailFormat($email);
                $this->validateGuestSubscription();
                $this->validateEmailAvailable($email);

                $status = $this->_subscriberFactory->create()->subscribe($email);
                if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE) {
                    $response = [
                        'status' => 'OK',
                        'msg' => 'The confirmation request has been sent.',
                    ];
                } else {
                    $response = [
                        'status' => 'OK',
                        'msg' => 'Thank you for your subscription.',
                    ];
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response = [
                    'status' => 'ERROR',
                    'msg' => __('There was a problem with the subscription: %1', $e->getMessage()),
                ];
            } catch (\Exception $e) {
                $response = [
                    'status' => 'ERROR',
                    'msg' => __('Something went wrong with the subscription.'),
                ];
            }
        }

        return $this->resultJsonFactory->create()->setData($response);

    }

}