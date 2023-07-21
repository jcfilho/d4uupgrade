<?php

namespace Magebay\Bookingsystem\Block\Adminhtml;
 
use Magento\Backend\Block\Template;
use Magebay\Bookingsystem\Helper\Data as BkHelper;

class RentPopup extends Template
{
	/**
     * @param \Magebay\Bookingsystem\Helper\Data
     * 
     */
	protected $_bkHelper;
	function __construct(
		\Magento\Backend\Block\Widget\Context $context,
		BkHelper $bkHelper,
		array $data = []
	)
	{
		$this->_bkHelper = $bkHelper;
		parent::__construct($context, $data);
	}
	function getBkRequestData()
	{
		$bookingId = $this->getBookingId();
		$bookingType = $this->getBookingType();
		$bookingTime = $this->getBookingTime();
		return array(
			'booking_id'=>$bookingId,
			'booking_type'=>$bookingType,
			'booking_time'=>$bookingTime
		);
	}
}