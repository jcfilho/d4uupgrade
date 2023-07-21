<?php

namespace Daytours\Wishlist\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
class Data extends AbstractHelper
{

    const OPTION_TEXT_TO_BOOKING = 'optionsbooking';
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /** @var \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet **/
    protected $_attributeSet;

    /**
     * block transfer.
     *
     * @var \Daytours\Bookingsystem\Helper\Data
     */
    private $_helperTransfer;


    protected $_registry;
    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;


    public function __construct(
        Context $context,
        Registry $coreRegistry,
        \Daytours\Bookingsystem\Helper\Data $helperTransfer,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    )
    {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_helperTransfer = $helperTransfer;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    public function setOptionsFromWishlistAddToCart($options,$productId){
        $result = [];
        $this->_coreRegistry->unregister('options_from_wishlist');

        if( $this->_helperTransfer->isBookingProduct($productId) ){
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
            $this->_coreRegistry->register('options_from_wishlist',$result);
        }

        return $result;
    }

    public function getParamsFromWishListRegistry($actionName){
        if ($actionName == 'wishlist_index_cart' || $actionName == 'wishlist_index_allcart'){
            $paramsregistry = $this->_coreRegistry->registry('options_from_wishlist');
            if( count($paramsregistry) > 0 ){
                return $this->_coreRegistry->registry('options_from_wishlist');
            }else{
                return false;
            }
        }
        return false;
    }


}
