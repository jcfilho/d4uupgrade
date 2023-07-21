<?php
namespace Daytours\Customer\Plugin\Block\Account;

use \Magento\Customer\Model\Url as UrlCustomer;

class AuthorizationLink{

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    public function __construct(
        UrlCustomer $customerUrl
    )
    {
        $this->_customerUrl = $customerUrl;
    }

    /**
     * @return string
     */
    public function aroundGetHref(\Magento\Customer\Block\Account\AuthorizationLink $subject, \Closure $proceed)
    {
        return $subject->isLoggedIn()
            ? $this->_customerUrl->getLogoutUrl()
            : '#';
    }

}