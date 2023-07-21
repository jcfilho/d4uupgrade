<?php

namespace Magebay\Bookingsystem\Block\Adminhtml\Facilities\Edit\From;



class FacilityTilte extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
	
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
	protected $_template = 'Magebay_Bookingsystem::facility/form/title_filed.phtml';
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\Json\Helper\Data $jsonHelper
	) 
	{
		$this->_coreRegistry = $registry;
		$this->_jsonHelper = $jsonHelper;
		parent::__construct($context);
	}
	
	public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
	{
	   $this->_element = $element;
	   $html = $this->toHtml();
	   return $html;
	}
	function getBkRequest()
	{
		$params = $this->_request->getParams();
		return $params;
	}
	function getFacility()
	{
		return $this->_coreRegistry->registry('facilities_data');
	}
	/** 
	* convert str to array
	* @params string $str
	* @return array $arrayData
	**/
	function getBkJsonDecode($str = '')
	{
		$arrayData = array();
		if($str != '')
		{
			$arrayData = $this->_jsonHelper->jsonDecode($str);
		}
		return $arrayData;
	}
}