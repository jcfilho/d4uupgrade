<?php
/**
 * Created by PhpStorm.
 * User: filho
 * Date: 5/13/18
 * Time: 8:12 AM
 */

namespace Daytours\Catalog\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;

class CustomImageProduct extends AbstractHelper
{
    const TYPE_CART = 'cart_img';
    const TYPE_HOME = 'home_img';

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * @param \Magento\Catalog\Model\Product $_product
     * @param $type
     * @return string
     */
    public function getImageByType(\Magento\Catalog\Model\Product $_product, $type){
        $existingMediaGalleryEntries = $_product->getMediaGalleryEntries();
        $idImage = null;

        foreach ($existingMediaGalleryEntries as $imageEntry){
            if( count($imageEntry->getTypes()) > 0 ){
                $types = $imageEntry->getTypes();
                if( in_array($type,$types) ){
                    return $this->_getUrl('') . 'pub/media/catalog/product' . $imageEntry->getFile();
                }
            }
        }

        return '';
    }

    public function getImageCustomTypeCart(\Magento\Catalog\Model\Product $_product){
        return $this->getImageByType($_product,self::TYPE_CART);
   }

    public function getImageCustomTypeHome(\Magento\Catalog\Model\Product $_product){
        return $this->getImageByType($_product,self::TYPE_HOME);
    }
}