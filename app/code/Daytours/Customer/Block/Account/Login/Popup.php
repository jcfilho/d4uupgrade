<?php

namespace Daytours\Customer\Block\Account\Login;

class Popup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session\Storage
     */
    protected $_storage;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session\Storage storage
     * @param \Magento\Customer\Model\Session $session
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session\Storage $storage,
        \Magento\Customer\Model\Session $session,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_storage = $storage;
        $this->_session = $session;
    }

    /**
     * Determine if the login popup should show up
     *
     * @return boolean
     */
    public function showUpPopup()
    {
        return $this->getRequest()->getParam('login_popup') == 'true' && !$this->_session->isLoggedIn();
    }

    /**
     * Retrieve the URL before authorization
     *
     * @return string
     */
    public function getBeforeAuthorizationUrl()
    {
        return $this->_storage->getData('before_auth_url');
    }
}
