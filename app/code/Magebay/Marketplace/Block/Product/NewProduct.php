<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Product;
class NewProduct extends \Magento\Framework\View\Element\Template{
	
	protected $_magebayData;
	
	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,        
		\Magebay\Marketplace\Helper\Data $magebayData,
        array $data = []		
	){		
		parent::__construct($context, $data);
		$this->_magebayData=$magebayData;
	}
	
	public function getOptionSetGroup(){
		$setOptionArray=$this->_magebayData->getOptionSetGroup();
		return $setOptionArray;
	}
}