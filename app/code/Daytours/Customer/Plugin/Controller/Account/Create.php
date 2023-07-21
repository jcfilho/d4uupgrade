<?php

namespace Daytours\Customer\Plugin\Controller\Account;

use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;

class Create{


    protected $resultRedirect;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManagerl;
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var Registration
     */
    private $registration;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $resultRedirectFactory;

    public function __construct(
        \Magento\Framework\Controller\ResultFactory $result,
        \Magento\Store\Model\StoreManagerInterface $storeManagerl,
        Session $customerSession,
        Registration $registration,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory

    )
    {
        $this->resultRedirect = $result;
        $this->storeManagerl = $storeManagerl;
        $this->customerSession = $customerSession;
        $this->registration = $registration;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function aroundExecute(\Magento\Customer\Controller\Account\Create $subject, \Closure $proceed)
    {

        if ($this->customerSession->isLoggedIn() || !$this->registration->isAllowed()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*');
            return $resultRedirect;
        }

        $homePage = $this->storeManagerl->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

        $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($homePage);
 
        return $resultRedirect;
    }
}