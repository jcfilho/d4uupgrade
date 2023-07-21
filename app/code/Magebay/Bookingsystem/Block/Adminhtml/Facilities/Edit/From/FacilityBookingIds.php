<?php

namespace Magebay\Bookingsystem\Block\Adminhtml\Facilities\Edit\From;

use Magebay\Bookingsystem\Helper\BkText as BkHelperText;

class FacilityBookingIds extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
	/**
     * Core RequestInterface
     *
     * @var \Magento\Framework\App\RequestInterface
     */
	protected $_request;
	/**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
	
	/**
     * Core Json Helper
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
	protected $_jsonHelper;
	/**
     * @param \Magebay\Bookingsystem\Helper\BkText
     * 
     */
	protected $_bkHelperText;
	protected $_template = 'Magebay_Bookingsystem::facility/form/booking_ids.phtml';
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Framework\Registry $registry,
		BkHelperText $bkHelperText
	) 
	{
		$this->_coreRegistry = $registry;
		$this->_bkHelperText = $bkHelperText;
		parent::__construct($context);
	}
	
	public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
	{
	   $this->_element = $element;
	   $html = $this->toHtml();
	   return $html;
	}
	function getFacility()
	{
		return $this->_coreRegistry->registry('facilities_data');
	}
	function getBkRequest()
	{
		return $this->_request;
	}
	function getArAjaxUrl()
	{
		$bkhelper = $this->_bkHelperText;
		$urlLoadBooking = $bkhelper->getBkAdminAjaxUrl('bookingsystem/facilities/loadBookings');
		return array(
			'url_load_booking'=>$urlLoadBooking,
		);
	}
}