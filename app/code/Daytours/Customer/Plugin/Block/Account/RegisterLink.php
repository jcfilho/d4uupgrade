<?php
namespace Daytours\Customer\Plugin\Block\Account;

use \Magento\Customer\Model\Url as UrlCustomer;

class RegisterLink{
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
    public function aroundGetHref(\Magento\Customer\Block\Account\RegisterLink $subject, \Closure $proceed)
    {
        return '#';
    }
}