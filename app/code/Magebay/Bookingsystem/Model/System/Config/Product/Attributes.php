<?php 

namespace Magebay\Bookingsystem\Model\System\Config\Product;

use Magento\Catalog\Model\Product;

class Attributes implements \Magento\Framework\Option\ArrayInterface
{

	protected $_productModel;

	public function __construct(Product $_productModel ) {
		$this->_productModel = $_productModel;
	}

	 /**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray( $isMultiselect = false)
	{
		$attributes = $this->_productModel->getAttributes();
		$options = array();
		foreach ($attributes as $attribute){
			if($attribute->getAttributecode() != '' && $attribute->getFrontendLabel() != '')
			{
				$options[] = array('label'=>$attribute->getFrontendLabel(),'value'=>$attribute->getAttributecode());
			}
			
		} 
       return $options;
	}
} 
