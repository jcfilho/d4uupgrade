<?php
namespace Magebay\Bookingsystem\Block\Adminhtml\Grid\Renderer\Bookingorders;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\Stdlib\DateTime\DateTime;
class FormatDate extends AbstractRenderer
{
	protected $_timeDate;
	function __construct(DateTime $dataTime)
	{
		$this->_timeDate = $dataTime;
	}
   public function render(\Magento\Framework\DataObject $row)
   {
		$timeDate = $this->_timeDate;
		$data = $this->_getValue($row);
		$data = $timeDate->gmtDate('M d, Y h:i:s A',$data);
		//$data = date('M d, Y h:i:s A',strtotime($data));
		return $data;
   }
}