<?php 
namespace Daytours\Bookingsystem\Observer\Frontend;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magebay\Bookingsystem\Helper\BkCustomOptions;
use Magento\Checkout\Model\Cart as CustomerCart;
use Daytours\Bookingsystem\Helper\Data;
class Extraoption extends \Magebay\Bookingsystem\Observer\Frontend\Extraoption {
    protected $http;
	/**
	* var Daytours\Bookingsystem\Helper\BkCustomOptions
	**/
    protected $_bkCustomOptions;
	protected $_cart;
	protected $_productMetadata;
    protected $_helperDaytours;

    /**
     * @var \Magento\Framework\Registry
     */

    protected $_registry;
    /**
     * @var \Daytours\Wishlist\Helper\Data
     */
    private $helperWishList;

    public function __construct(
        \Magento\Framework\App\Request\Http $http,
		\Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
		BkCustomOptions $bkCustomOptions,
        \Daytours\Bookingsystem\Helper\Data $helperDaytours,
        \Magento\Framework\Registry $registry,
        \Daytours\Wishlist\Helper\Data $helperWishList
    ) {
        $this->http = $http;
		$this->_cart = $cart;
		$this->_bkCustomOptions = $bkCustomOptions;
		$this->_productMetadata = $productMetadata;
        $this->_helperDaytours = $helperDaytours;
        $this->_registry = $registry;
        parent::__construct(
            $http,
            $cart,
            $productMetadata,
            $bkCustomOptions);
        $this->helperWishList = $helperWishList;
    }
    public function execute(\Magento\Framework\Event\Observer $observer) {
        // set the additional options on the product
		$actionName = "";
		try {
            $action = $this->http;
            if($action) {
                $actionName = $this->http->getFullActionName();    
            }
		} catch(\Exception $e) {
			$actionName = "";
		}
        if ($actionName == 'checkout_cart_add' || $actionName == 'wishlist_index_cart' || $actionName == 'wishlist_index_allcart')
        {
            // assuming you are posting your custom form values in an array called extra_options...
            $params = $this->http->getParams();
            if ($actionName == 'wishlist_index_cart' || $actionName == 'wishlist_index_allcart'){
                $paramsRegistryFromWishlist = $this->helperWishList->getParamsFromWishListRegistry($actionName);
                if( $paramsRegistryFromWishlist  ){
                    $params = $paramsRegistryFromWishlist;
                }else{
                    return;
                }
            }

            if (count($params))
            {
				if(isset($params['bk_item_id']) && (int)$params['bk_item_id'] > 0)
				{
					$bkItemId = (int)$params['bk_item_id'];
					$this->_cart->removeItem($bkItemId);
				}
                $product = $observer->getProduct();
                if( isset($params['isRoundTrip']) ){
                //if( $isTransferProduct ){
                    $bookingOPtions = $this->_bkCustomOptions->createExtractOptionsByTransfer($product,$params);
                }else{
                    $bookingOPtions = $this->_bkCustomOptions->createExtractOptions($product,$params);
                }

				if($bookingOPtions['status'] == false)
				{
					throw new \Exception(__('Dates are not available. Please check again!'));
				}
				else
				{
                    $version = $this->_productMetadata->getVersion();
                    if(version_compare($version, '2.2.0') >= 0)
                    {
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        $json = $objectManager->get('Magento\Framework\Serialize\Serializer\Json');
                        $additionalOptions = $bookingOPtions['bk_options'];
                        $jsonOptions = $json->serialize($additionalOptions);
                        $observer->getProduct()
                            ->addCustomOption('additional_options', $jsonOptions);
                    }
                    else{
                        $additionalOptions = $bookingOPtions['bk_options'];
                        $observer->getProduct()
                            ->addCustomOption('additional_options', serialize($additionalOptions));
                    }
				}               
            }
        }
    }

}