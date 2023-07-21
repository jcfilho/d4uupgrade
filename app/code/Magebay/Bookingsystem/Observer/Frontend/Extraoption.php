<?php 
namespace Magebay\Bookingsystem\Observer\Frontend;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magebay\Bookingsystem\Helper\BkCustomOptions;
use Magento\Checkout\Model\Cart as CustomerCart;
class Extraoption implements ObserverInterface {
    protected $http;
	/**
	* var Magebay\Bookingsystem\Helper\BkCustomOptions
	**/
    protected $_bkCustomOptions;
	protected $_cart;
	protected $_productMetadata;
    public function __construct(
        \Magento\Framework\App\Request\Http $http,
		\Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
		BkCustomOptions $bkCustomOptions
    ) {
        $this->http = $http;
		$this->_cart = $cart;
		$this->_bkCustomOptions = $bkCustomOptions;
		$this->_productMetadata = $productMetadata;
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
        if ($actionName == 'checkout_cart_add')
        {
            // assuming you are posting your custom form values in an array called extra_options...
            $params = $this->http->getParams();
            if (count($params))
            {
				if(isset($params['bk_item_id']) && (int)$params['bk_item_id'] > 0)
				{
					$bkItemId = (int)$params['bk_item_id'];
					$this->_cart->removeItem($bkItemId);
				}
                $product = $observer->getProduct();
				$bookingOPtions = $this->_bkCustomOptions->createExtractOptions($product,$params);
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