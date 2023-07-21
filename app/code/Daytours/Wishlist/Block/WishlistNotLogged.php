<?php

namespace Daytours\Wishlist\Block;

/**
 * Created by PhpStorm.
 * User: jose
 * Date: 8/18/18
 * Time: 3:12 PM
 */
use \Magento\Framework\View\Element\Template;
use \Magento\Customer\Model\Session;

class WishlistNotLogged extends Template{

    /**
     * @var Session
     */
    private $session;

    /**
     * WishlistNotLogged constructor.
     * @param Session $session
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Session $session,
        Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->session = $session;
    }


    /**
     * Return true if customer is logged
     * @return bool
     */
    public function isCustomerIsLogged(){
        if($this->session->isLoggedIn()){
            return true;
        }
        return false;
    }

}