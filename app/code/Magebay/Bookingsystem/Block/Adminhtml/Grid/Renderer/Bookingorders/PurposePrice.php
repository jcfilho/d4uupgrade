<?php
namespace Magebay\Bookingsystem\Block\Adminhtml\Grid\Renderer\Bookingorders;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Sales\Model\Order;
class PurposePrice extends AbstractRenderer
{
	/**
	* @var Magento\Sales\Model\Order
	*/
	protected $_modelOrder;
	 /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
	 
    protected $_localeCurrency;
	function __construct(
		Order $modelOrder,
		\Magento\Framework\Locale\CurrencyInterface $localeCurrency
	)
	{
		$this->_localeCurrency = $localeCurrency;
		$this->_modelOrder = $modelOrder;
	}
   public function render(\Magento\Framework\DataObject $row)
   {
		$orderId = $row->getId();
		$order = $this->_modelOrder;
		$order->load($orderId);
		$finalPrice = $this->_getValue($row);
		if($order->getId())
		{
			$orderCurrencyCode = $order->getOrderCurrencyCode();
			$finalPrice = $this->_localeCurrency->getCurrency($orderCurrencyCode)->toCurrency($finalPrice);
		}
		return $finalPrice;
   }
}