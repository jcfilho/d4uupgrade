<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Product\CustomOpt\Options\Type;
class AbstractType extends \Magento\Framework\View\Element\Template{

    /**
     * @var string
     */
    protected $_name = 'abstract';

    /**
     * @var \Magento\Catalog\Model\Config\Source\Product\Options\Price
     */
    protected $_optionPrice;
	
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,	
		\Magento\Catalog\Model\Config\Source\Product\Options\Price $optionPrice,
		array $data = []
	){
		$this->_optionPrice = $optionPrice;
		parent::__construct($context, $data);
	}	

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
		$this->setOptionPriceType();
		$this->getOptionPriceType();
        return parent::_prepareLayout();
    }

	protected function setOptionPriceType() {
	
        $this->setChild(
            'option_price_type',
            $this->getLayout()->addBlock(
                'Magento\Framework\View\Element\Html\Select',
                $this->getNameInLayout() . '.option_price_type',
                $this->getNameInLayout()
            )->setData(
                [
                    'id' => 'product_option_<%- data.option_id %>_price_type',
                    'class' => 'select product-option-price-type',
                ]
            )
        );	
		return $this;
	}
	
	protected function getOptionPriceType() {
        $this->getChildBlock(
            'option_price_type'
        )->setName(
            'product[options][<%- data.option_id %>][price_type]'
        )->setOptions(
            $this->_optionPrice->toOptionArray()
        );	
		return $this;
	}
    /**
     * Get html of Price Type select element
     *
     * @param string $extraParams
     * @return string
     */
    public function getProPriceTypeSelectHtml($extraParams = '')
    {
        if ($this->getCanEditPrice() === false) {
            $extraParams .= ' disabled="disabled"';
            $this->getChildBlock('option_price_type');
        }
        $this->getChildBlock('option_price_type')->setExtraParams($extraParams);

        return $this->getChildHtml('option_price_type');
    }
}