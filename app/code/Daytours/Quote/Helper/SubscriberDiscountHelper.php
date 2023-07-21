<?php
/**
 * @author josecarlos.filhov@gmail.com
 * @copyright Daytours4u
 */

namespace Daytours\Quote\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterfaceAlias;
use Magento\Newsletter\Model\ResourceModel\SubscriberFactory;
use Magento\Newsletter\Model\Subscriber;
use \Magento\Quote\Api\CartRepositoryInterface;

class SubscriberDiscountHelper
{

    const COUPON_CODE_TO_NEWSLETTER = 'newsletterdt/general/coupon';

    /**
     * @var ScopeConfigInterfaceAlias
     */
    private $scopeConfig;
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * SubscriberDiscountHelper constructor.
     * @param ScopeConfigInterfaceAlias $scopeConfig
     * @param SubscriberFactory $subscriberFactory
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        ScopeConfigInterfaceAlias $scopeConfig,
        SubscriberFactory $subscriberFactory,
        CartRepositoryInterface $cartRepository
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->subscriberFactory = $subscriberFactory;
        $this->cartRepository = $cartRepository;
    }


    public function validateDiscountToSubscriber($couponCode,$email){

        $couponCodeForNewsletterSubscriber = $this->scopeConfig->getValue(self::COUPON_CODE_TO_NEWSLETTER);

        if( $couponCode == $couponCodeForNewsletterSubscriber ){
            if( !$this->ifEmailIsSubscribe($email) ){
                return false;
            }
        }

        return true;
    }

    /**
     * @param $email
     * @return array|bool|Subscriber
     */
    private function ifEmailIsSubscribe($email){

        /** @var \Magento\Newsletter\Model\ResourceModel\Subscriber $subscriberFactory */
        /** @var Subscriber $subscriber */
        $subscriberFactory = $this->subscriberFactory->create();

        $subscriber = $subscriberFactory->loadByEmail($email);
        if( $subscriber ){
            if( $subscriber['subscriber_status'] == Subscriber::STATUS_SUBSCRIBED ){
                return $subscriber;
            }
        }

        return false;
    }

}