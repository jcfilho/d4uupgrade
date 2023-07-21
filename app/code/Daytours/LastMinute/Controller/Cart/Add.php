<?php

namespace Daytours\LastMinute\Controller\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Daytours\LastMinute\Model\Product as LastminuteProduct;

class Add extends \Magento\Checkout\Controller\Cart\Add
{

    private $lastMinuteProductAvailable = false;
    /**
     * @var LastminuteProduct
     */
    protected $lastminuteProduct;
    /**
     * @var \Daytours\Bookingsystem\Helper\Data
     */
    private $dataBookingSystem;
    /**
     * @var \Magebay\Bookingsystem\Helper\BkHelperDate
     */
    private $bkHelperDate;
    /**
     * @var \Daytours\LastMinute\Helper\LastMinute
     */
    private $lastMinuteHelper;


    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param LastminuteProduct $lastminuteProduct
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        LastminuteProduct $lastminuteProduct,
        \Daytours\Bookingsystem\Helper\Data $dataBookingSystem,
        \Magebay\Bookingsystem\Helper\BkHelperDate $bkHelperDate,
        \Daytours\LastMinute\Helper\LastMinute $lastMinuteHelper
    )
    {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $productRepository
        );
        $this->lastminuteProduct = $lastminuteProduct;
        $this->dataBookingSystem = $dataBookingSystem;

        $this->bkHelperDate = $bkHelperDate;
        $this->lastMinuteHelper = $lastMinuteHelper;
    }

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $params = $this->getRequest()->getParams();

        try {

            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get(
                        \Magento\Framework\Locale\ResolverInterface::class
                    )->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            //if is last minute, verfy if is posible to add like last minute
            $isToday = $this->dataBookingSystem->isToday($params['check_in']);
            $isTomorrow = $this->dataBookingSystem->isTomorrow($params['check_in']);

            if( ($isToday || $isTomorrow ) ){
                if( $this->lastMinuteHelper->ifHasLastminute($product) ){
                    if ( !$this->lastminuteProduct->isLastMinute($product, $this->getRequest()->getParams()) ) {
                        $this->messageManager->addError(
                            $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml(__('We can not book the service now, try to book for tomorrow please.'))
                        );
                        return $this->goBack();
                    }
                    $this->lastMinuteProductAvailable = true;
                }else{
                    $this->messageManager->addError(
                        $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml(__('We can not add the product in this date, we need at least 2 days to book the service.'))
                    );
                    return $this->goBack();
                }
            }


            /**
             * Check product availability
             */
            if (!$product) {
                return $this->goBack();
            }

            $this->cart->addProduct($product, $params);
            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }

            $this->cart->save();

            /**
             * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
             */
            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );

            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                if (!$this->cart->getQuote()->getHasError()) {
                    $message = __(
                        'You added %1 to your shopping cart.',
                        $product->getName()
                    );
                    $this->messageManager->addSuccessMessage($message);
                }
                return $this->goBack(null, $product);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNotice(
                    $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage())
                );
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addError(
                        $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($message)
                    );
                }
            }

            $url = $this->_checkoutSession->getRedirectUrl(true);

            if (!$url) {
                $cartUrl = $this->_objectManager->get(\Magento\Checkout\Helper\Cart::class)->getCartUrl();
                $url = $this->_redirect->getRedirectUrl($cartUrl);
            }

            return $this->goBack($url);
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            return $this->goBack();
        }
    }


    /**
     * Resolve response
     *
     * @param string $backUrl
     * @param \Magento\Catalog\Model\Product $product
     * @return $this|\Magento\Framework\Controller\Result\Redirect
     */
    protected function goBack($backUrl = null, $product = null)
    {
        if (!$this->getRequest()->isAjax()) {
            return parent::_goBack($backUrl);
        }

        $result = [];

        if ($backUrl || $backUrl = $this->getBackUrl()) {
            $result['backUrl'] = $backUrl;
        } else {
            if ($product && !$product->getIsSalable()) {
                $result['product'] = [
                    'statusText' => __('Out of stock')
                ];
            }
        }

        if( $this->lastMinuteProductAvailable && $this->lastMinuteHelper->ifHasLastminute($product) ){
            $backUrl = $this->_url->getUrl('checkout');
            $result['backUrl'] = $backUrl;
            $result['lastMinute'] = 1;
        }

        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
        );
    }
}