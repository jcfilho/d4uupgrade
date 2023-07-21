<?php 
/**
* code for 2.1 or more
**/
namespace Magebay\Bookingsystem\Block\Marketplace;

use Magento\Framework\View\Element\Template;
use Magebay\Bookingsystem\Helper\Data as BkHelper;

class Tab extends Template
{
	protected $_product;
	/**
     * @param \Magebay\Bookingsystem\Helper\Data
     * 
     */
	protected $_template = 'Magebay_Bookingsystem::marketplace/mk_edit21.phtml';
	protected $_bkHelper;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
		\Magento\Catalog\Model\Product $product,
		BkHelper $bkHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
		$this->_product = $product;
		$this->_bkHelper = $bkHelper;
    }
	function getFromAjaxUrl()
	{
		$bkhelper = $this->_bkHelper;
		$url = $bkhelper->formatUrlPro($this->getUrl('bookingsystem/marketplace/GetFromBk'));
		return $url;
	}
	function getCoreProduct($productId)
	{
		return $this->_product->load($productId);
	}
	function getBkHelper()
	{
		return $this->_bkHelper;
	}
}

?>