<?php

namespace Daytours\Customer\Plugin\Controller\Account;

use Magento\Framework\Controller\ResultFactory;
use \Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Customer\Model\Session;

class Login{

    protected $_storeManager;
    protected $resultRedirect;
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    public function __construct(
        \Magento\Framework\Controller\ResultFactory $result,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Session $customerSession,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
    )
    {
        $this->resultRedirect = $result;
        $this->_storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function aroundExecute(\Magento\Customer\Controller\Account\Login $subject, \Closure $proceed)
    {

        if ($this->customerSession->isLoggedIn()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        $homePage = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB) . '?login_popup=true';

        $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($homePage);
        return $resultRedirect;
    }
}