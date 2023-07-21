<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
 
namespace Magebay\Marketplace\Block\Product;
class Gallery extends \Magento\Framework\View\Element\Template{
	
	protected $_product;
	
    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $_mediaConfig;

	    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Framework\File\Size
     */
    protected $_fileSizeService;
	
	protected $_coreRegistry = null;
	
	/**
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
	 * @param \Magento\Framework\File\Size $fileSize
	 * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
	 * @param \Magento\Framework\Registry $coreRegistry
	 */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,		
		\Magento\Catalog\Model\Product\Media\Config $mediaConfig,
		\Magento\Framework\File\Size $fileSize,
		\Magento\Framework\Json\EncoderInterface $jsonEncoder,
		\Magento\Framework\Registry $coreRegistry,
        array $data = []
    )
    {
        parent::__construct($context, $data);              			
		
        $this->_jsonEncoder = $jsonEncoder;
		
        $this->_mediaConfig = $mediaConfig;
		
		$this->_fileSizeService = $fileSize;
        $this->_coreRegistry = $coreRegistry;
    }
	
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }	
	
    /**
     * @return string
     */
    public function getGalleryImagesJson()
    {		
		$product=$this->getProduct();		
		$mediaGalleryData = $product->getData('media_gallery');								
		
        if (is_array($mediaGalleryData)) {
            $value = $mediaGalleryData;
            if (is_array($value['images']) && count($value['images']) > 0) {
				
                foreach ($value['images'] as &$image) {
                    $image['url'] = $this->_mediaConfig->getMediaUrl($image['file']);					
                }
                return $this->_jsonEncoder->encode($value['images']);
            }
        }
        return '[]';
    }
	
	public function getProMediaAttributes(){
		$product=$this->getProduct();
		return $product->getMediaAttributes();
	}
	
    /**
     * Get image types data
     *
     * @return array
     */
    public function getProImageTypes()
    {
		$product=$this->getProduct();
		$imageTypes = [];		
		foreach( $product->getMediaAttributes() as $attribute){
            $imageTypes[$attribute->getAttributeCode()] = [
                'code' => $attribute->getAttributeCode(),
                'value' => $attribute->getFrontend()->getValue($product),
                'label' => $attribute->getStoreLabel(),
                'name' => 'product['.$attribute->getAttributeCode().']',
            ];					
		}			
        return $imageTypes;
    }

	public function getFileSizeService(){
		return $this->_fileSizeService;
	}	
}