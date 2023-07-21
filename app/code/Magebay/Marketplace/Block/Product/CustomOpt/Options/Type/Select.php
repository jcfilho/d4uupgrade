<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Product\CustomOpt\Options\Type;

class Select extends \Magebay\Marketplace\Block\Product\CustomOpt\Options\Type\AbstractType
{
    /**
     * @var string
     */
    protected $_template = 'Magebay_Marketplace::product/customoptions/options/type/select.phtml';

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setCanEditPrice(true);
        $this->setCanReadPrice(true);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    { 

		$this->addNewRowButton();
		$this->addDeleteRowButton();

        return parent::_prepareLayout();
    }
	
	protected function addNewRowButton() {
        $this->addChild(
            'add_select_row_button',
            'Magebay\Marketplace\Block\Product\Widget\Button',
            [
                'label' => __('Add New Row'),
                'class' => 'add add-select-row',
                'id' => 'product_option_<%- data.option_id %>_add_select_row'
            ]
        );
		return $this;
	}
	
	protected function addDeleteRowButton() {
        $this->addChild(
            'delete_select_row_button',
            'Magebay\Marketplace\Block\Product\Widget\Button',
            [
                'label' => __('Delete Row'),
                'class' => 'delete delete-select-row icon-btn',
                'id' => 'product_option_<%- data.id %>_select_<%- data.select_id %>_delete'
            ]
        );
		return $this;		
	}

    /**
     * Return select input for price type
     *
     * @param string $extraParams
     * @return string
     */
    public function getProPriceTypeSelectHtml($extraParams = '')
    {
        $this->getChildBlock(
            'option_price_type'
        )->setData(
            'id',
            'product_option_<%- data.id %>_select_<%- data.select_id %>_price_type'
        )->setName(
            'product[options][<%- data.id %>][values][<%- data.select_id %>][price_type]'
        );

        return parent::getProPriceTypeSelectHtml($extraParams);
    }
}
