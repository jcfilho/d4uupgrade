<?php

namespace Magebay\Bookingsystem\Block\Adminhtml\Grid\Renderer\Bookingorders;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
class FormatBasePrice extends AbstractRenderer
{
	/**
     * @var PriceHelper
     */
	protected $_priceHelper;
	
	function __construct(
		PriceHelper $priceHelper
	)
	{
		$this->_priceHelper = $priceHelper;
	}
   public function render(\Magento\Framework\DataObject $row)
   {
		$basePrice = $this->_getValue($row);
		$priceHelper = $this->_priceHelper;
		$basePrice = $priceHelper->currency(number_format($basePrice,2),true,false);
		return $basePrice;
   }
}