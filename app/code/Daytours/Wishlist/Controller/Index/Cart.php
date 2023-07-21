<?php
namespace Daytours\Wishlist\Controller\Index;

use Magento\Framework\App\Action;
use Magento\Catalog\Model\Product\Exception as ProductException;
use Magento\Framework\Controller\ResultFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Cart extends \Magento\Wishlist\Controller\AbstractIndex
{
    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Wishlist\Model\LocaleQuantityProcessor
     */
    protected $quantityProcessor;

    /**
     * @var \Magento\Wishlist\Model\ItemFactory
     */
    protected $itemFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * @var \Magento\Wishlist\Model\Item\OptionFactory
     */
    private $optionFactory;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Magento\Framework\Registry
     */

    protected $_registry;
    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * block transfer.
     *
     * @var \Daytours\Bookingsystem\Helper\Data
     */
    private $_helperTransfer;

    /**
     * helper whislist daytours.
     *
     * @var \Daytours\Wishlist\Helper\Data
     */
    private $_helperWishlist;



    /**
     * @param Action\Context $context
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param \Magento\Wishlist\Model\LocaleQuantityProcessor $quantityProcessor
     * @param \Magento\Wishlist\Model\ItemFactory $itemFactory
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Wishlist\Model\Item\OptionFactory $optionFactory
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Wishlist\Helper\Data $helper
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Action\Context $context,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        \Magento\Wishlist\Model\LocaleQuantityProcessor $quantityProcessor,
        \Magento\Wishlist\Model\ItemFactory $itemFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Wishlist\Model\Item\OptionFactory $optionFactory,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper $escaper,
        \Magento\Wishlist\Helper\Data $helper,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        \Daytours\Bookingsystem\Helper\Data $helperTransfer,
        \Daytours\Wishlist\Helper\Data $helperWishlist
    ) {
        $this->wishlistProvider = $wishlistProvider;
        $this->quantityProcessor = $quantityProcessor;
        $this->itemFactory = $itemFactory;
        $this->cart = $cart;
        $this->optionFactory = $optionFactory;
        $this->productHelper = $productHelper;
        $this->escaper = $escaper;
        $this->helper = $helper;
        $this->cartHelper = $cartHelper;
        $this->formKeyValidator = $formKeyValidator;
        $this->_registry = $registry;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->_helperTransfer = $helperTransfer;
        $this->_helperWishlist = $helperWishlist;
        parent::__construct($context);
    }

    /**
     * Add wishlist item to shopping cart and remove from wishlist
     *
     * If Product has required options - item removed from wishlist and redirect
     * to product view page with message about needed defined required options
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/*/');
        }

        $itemId = (int)$this->getRequest()->getParam('item');
        /* @var $item \Magento\Wishlist\Model\Item */
        $item = $this->itemFactory->create()->load($itemId);
        if (!$item->getId()) {
            $resultRedirect->setPath('*/*');
            return $resultRedirect;
        }
        $wishlist = $this->wishlistProvider->getWishlist($item->getWishlistId());
        if (!$wishlist) {
            $resultRedirect->setPath('*/*');
            return $resultRedirect;
        }

        // Set qty
        $qty = $this->getRequest()->getParam('qty');
        $postQty = $this->getRequest()->getPostValue('qty');
        if ($postQty !== null && $qty !== $postQty) {
            $qty = $postQty;
        }
        if (is_array($qty)) {
            if (isset($qty[$itemId])) {
                $qty = $qty[$itemId];
            } else {
                $qty = 1;
            }
        }
        $qty = $this->quantityProcessor->process($qty);
        if ($qty) {
            $item->setQty($qty);
        }

        $redirectUrl = $this->_url->getUrl('*/*');
        $configureUrl = $this->_url->getUrl(
            '*/*/configure/',
            [
                'id' => $item->getId(),
                'product_id' => $item->getProductId(),
            ]
        );

        try {
            /** @var \Magento\Wishlist\Model\ResourceModel\Item\Option\Collection $options */
            $options = $this->optionFactory->create()->getCollection()->addItemFilter([$itemId]);
            $item->setOptions($options->getOptionsByItem($itemId));

            $buyRequest = $this->productHelper->addParamsToBuyRequest(
                $this->getRequest()->getParams(),
                ['current_config' => $item->getBuyRequest()]
            );

            $item->mergeBuyRequest($buyRequest);
            $this->_helperWishlist->setOptionsFromWishlistAddToCart($options->getOptionsByItem($itemId),$item->getProductId());
            $item->addToCart($this->cart, true);
            $this->cart->save()->getQuote()->collectTotals();
            $wishlist->save();

            if (!$this->cart->getQuote()->getHasError()) {
                $message = __(
                    'You added %1 to your shopping cart.',
                    $this->escaper->escapeHtml($item->getProduct()->getName())
                );
                $this->messageManager->addSuccess($message);
            }

            if ($this->cartHelper->getShouldRedirectToCart()) {
                $redirectUrl = $this->cartHelper->getCartUrl();
            } else {
                $refererUrl = $this->_redirect->getRefererUrl();
                if ($refererUrl && $refererUrl != $configureUrl) {
                    $redirectUrl = $refererUrl;
                }
            }
        } catch (ProductException $e) {
            $this->messageManager->addError(__('This product(s) is out of stock.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addNotice($e->getMessage());
            $redirectUrl = $configureUrl;
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t add the item to the cart right now.'));
        }

        $this->helper->calculate();

        if ($this->getRequest()->isAjax()) {
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData(['backUrl' => $redirectUrl]);
            return $resultJson;
        }

        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
    }

    private function setOptionsFromWishlistAddToCart($options,$productId){
        $result = [];

        if( $this->_helperTransfer->ifProductIsTransfer($productId) ){
            $resultOptions  = [];
            foreach ($options as $item){
                if( $item->getCode() == 'info_buyRequest' ){
                    $data = $this->serializer->unserialize($item->getValue());
                    $result = $data;
                    break;
                }
            }

            foreach ($result['optionsbooking'] as $keyB => $itemB){
                if( $keyB == 'goingroundtrip' ){
                    $result['isRoundTrip'] = ($itemB == 1) ? 0 : 1;
                }else if($keyB == 'interval_one'){
                    $result['intervals_hours'] = $itemB;
                }elseif ($keyB == 'interval_two'){
                    $result['intervals_hours_two'] = $itemB;
                }else{
                    $result[$keyB] = $itemB;
                }

            }

            $this->_registry->register('options_from_wishlist',$result);
        }

        return $result;
    }
}
