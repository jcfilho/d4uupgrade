<?php
namespace Magebay\Bookingsystem\Block\Adminhtml\Form\Renderer\Config;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
class Activestatus extends Field
{
    protected $bookingsystemHelper;
    protected $actModelFactory;
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magebay\Bookingsystem\Helper\Data $bookingsystemHelper,
        \Magebay\Bookingsystem\Model\ActFactory $actModelFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->bookingsystemHelper = $bookingsystemHelper;
        $this->actModelFactory = $actModelFactory;
    }
    protected function _getElementHtml(AbstractElement $element)
    {
        $main_domain = $this->bookingsystemHelper->get_domain( $_SERVER['SERVER_NAME'] );
		if ( $main_domain != 'dev' ) {
            $rakes = $this->actModelFactory->create()->getCollection();
            $rakes->addFieldToFilter('path', 'bookingsystem/act/key' );
            $valid = false;
            if ( count($rakes) > 0 ) {
                foreach ( $rakes as $rake )  {
                    if ( $rake->getExtensionCode() == md5($main_domain.trim($this->bookingsystemHelper->getStoreConfigData('bookingsystem/act/key')) ) ) {
                        $valid = true;	
                    }
                }
            }	
            $html = base64_decode('PHAgc3R5bGU9ImNvbG9yOiByZWQ7Ij48Yj5OT1QgVkFMSUQ8L2I+PC9wPjxhIGhyZWY9Imh0dHA6Ly9ib29raW5nc3lzdGVtcHJvLmNvbS8jcHJpY2luZyIgdGFyZ2V0PSJfYmxhbmsiPlZpZXcgUHJpY2U8L2E+PC9icj4=');	
            if ( $valid == true ) {
            //if ( count($rakes) > 0 ) {  
                foreach ( $rakes as $rake )  {
                    if ( $rake->getExtensionCode() == md5($main_domain.trim($this->bookingsystemHelper->getStoreConfigData('bookingsystem/act/key')) ) ) {
                        $html = base64_decode('PGhyIHdpZHRoPSIyODAiPjxiPltEb21haW5Db3VudF0gRG9tYWluIExpY2Vuc2U8L2I+PGJyPjxiPkFjdGl2ZSBEYXRlOiA8L2I+W0NyZWF0ZWRUaW1lXTxicj48YSBocmVmPSJodHRwOi8vYm9va2luZ3N5c3RlbXByby5jb20vI3ByaWNpbmciIHRhcmdldD0iX2JsYW5rIj5WaWV3IFByaWNlPC9hPjxicj4=');	
                        $html = str_replace(array('[DomainCount]','[CreatedTime]'),array($rake->getDomainCount(),$rake->getCreatedTime()),$html);
                    }
                }
            }
		} else { 
		    $html = base64_decode('PHAgc3R5bGU9ImNvbG9yOiByZWQ7Ij48Yj5OT1QgVkFMSUQ8L2I+PC9wPjxhIGhyZWY9Imh0dHA6Ly9ib29raW5nc3lzdGVtcHJvLmNvbS8jcHJpY2luZyIgdGFyZ2V0PSJfYmxhbmsiPlZpZXcgUHJpY2U8L2E+PC9icj4=');	
		}	
        return $html;       
    }
}