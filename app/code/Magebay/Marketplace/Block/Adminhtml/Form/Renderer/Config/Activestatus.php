<?php
namespace Magebay\Marketplace\Block\Adminhtml\Form\Renderer\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Activestatus extends Field
{
    protected $marketplaceHelper;
    protected $actModelFactory;
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magebay\Marketplace\Helper\Data $marketplaceHelper,
        \Magebay\Marketplace\Model\ActFactory $actModelFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->marketplaceHelper = $marketplaceHelper;
        $this->actModelFactory = $actModelFactory;
    }
    protected function _getElementHtml(AbstractElement $element)
    {
        $main_domain = $this->marketplaceHelper->get_domain( $_SERVER['SERVER_NAME'] );
		if ( $main_domain != 'dev' ) {
            $rakes = $this->actModelFactory->create()->getCollection();
            $rakes->addFieldToFilter('path', 'marketplace/act/key' );
            $valid = false;
            if ( count($rakes) > 0 ) {
                foreach ( $rakes as $rake )  {
                    if ( $rake->getExtensionCode() == md5($main_domain.trim($this->marketplaceHelper->getStoreConfigData('marketplace/act/key')) ) ) {
                        $valid = true;	
                    }
                }
            }	
            $html = base64_decode('PHAgc3R5bGU9ImNvbG9yOiByZWQ7Ij48Yj5OT1QgVkFMSUQ8L2I+PC9wPjxhIGhyZWY9Imh0dHBzOi8vd3d3Lm1hZ2ViYXkuY29tLyIgdGFyZ2V0PSJfYmxhbmsiPlZpZXcgUHJpY2U8L2E+PC9icj4=');	
            if ( $valid == true ) {
            //if ( count($rakes) > 0 ) {  
                foreach ( $rakes as $rake )  {
                    if ( $rake->getExtensionCode() == md5($main_domain.trim($this->marketplaceHelper->getStoreConfigData('marketplace/act/key')) ) ) {
                        $html = base64_decode('PGhyIHdpZHRoPSIyODAiPjxiPltEb21haW5Db3VudF0gRG9tYWluIExpY2Vuc2U8L2I+PGJyPjxiPkFjdGl2ZSBEYXRlOiA8L2I+W0NyZWF0ZWRUaW1lXTxicj48YSBocmVmPSJodHRwczovL3d3dy5tYWdlYmF5LmNvbS8iIHRhcmdldD0iX2JsYW5rIj5WaWV3IFByaWNlPC9hPjxicj4=');	
                        $html = str_replace(array('[DomainCount]','[CreatedTime]'),array($rake->getDomainCount(),$rake->getCreatedTime()),$html);
                    }
                }
            }
		} else { 
		    $html = base64_decode('PHAgc3R5bGU9ImNvbG9yOiByZWQ7Ij48Yj5OT1QgVkFMSUQ8L2I+PC9wPjxhIGhyZWY9Imh0dHBzOi8vd3d3Lm1hZ2ViYXkuY29tLyIgdGFyZ2V0PSJfYmxhbmsiPlZpZXcgUHJpY2U8L2E+PC9icj4=');	
		}	
        return $html;       
    }
}