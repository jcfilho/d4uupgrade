<?php


namespace Daytours\Quote\Plugin;


use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\CouponManagement as CouponManagementAlias;
use Magento\Quote\Model\Quote;
use Daytours\Quote\Helper\SubscriberDiscountHelper;

class ApplyCoupon
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;
    /**
     * @var SubscriberDiscountHelper
     */
    private $subscriberDiscountHelper;


    /**
     * ApplyCoupon constructor.
     * @param CartRepositoryInterface $cartRepository
     * @param SubscriberDiscountHelper $subscriberDiscountHelper
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        SubscriberDiscountHelper $subscriberDiscountHelper
    )
    {
        $this->cartRepository = $cartRepository;
        $this->subscriberDiscountHelper = $subscriberDiscountHelper;
    }

    /**
     * @param CouponManagementAlias $subject
     * @param $cartId
     * @param $couponCode
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function beforeSet(CouponManagementAlias $subject, $cartId, $couponCode)
    {

        /** @var Quote $quote */
        $quote = $this->cartRepository->getActive($cartId);
        $email = $quote->getCustomerEmail();

        if( !$this->subscriberDiscountHelper->validateDiscountToSubscriber($couponCode,$email)){
            throw new CouldNotSaveException(__('Could not apply coupon code'));
        }

    }


}