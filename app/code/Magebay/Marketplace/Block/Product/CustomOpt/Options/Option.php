<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Product\CustomOpt\Options;
class Option extends \Magento\Framework\View\Element\Template{
    
	/**
     * @var string
     */
    //protected $_template = 'product/customoptions/options/option.phtml';
	
    /**
     * @var int
     */
    protected $_itemCount = 1;
	
    /**
     * @var \Magento\Framework\DataObject[]
     */
    protected $_values;
	
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $_configYesNo;
	
    /**
     * @var Product
     */
    protected $_productInstance;
	
    /**
     * @var \Magento\Catalog\Model\ProductOptions\ConfigInterface
     */
    protected $_productOptionConfig;	
	
    /**
     * @var \Magento\Catalog\Model\Config\Source\Product\Options\Type
     */
    protected $_optionType;
	
	protected $_coreRegistry = null;
	
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,	
		\Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig,
		\Magento\Catalog\Model\Config\Source\Product\Options\Type $optionType,
		\Magento\Config\Model\Config\Source\Yesno $configYesNo,
		\Magento\Framework\Registry $coreRegistry,
		array $data = []			
	){
		parent::__construct($context, $data);
		$this->_optionType = $optionType;
		$this->_configYesNo = $configYesNo;
		$this->_productOptionConfig = $productOptionConfig;
		$this->_coreRegistry = $coreRegistry;
	}
	
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setCanReadPrice(true);
        $this->setCanEditPrice(true);
    }
	
    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        foreach ($this->_productOptionConfig->getAll() as $option) {
			$renderer=$option['renderer'];
			switch($renderer){
				case 'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Type\Text':
					$option['renderer']='Magebay\Marketplace\Block\Product\CustomOpt\Options\Type\Text';
					break;
				case 'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Type\File':
					$option['renderer']='Magebay\Marketplace\Block\Product\CustomOpt\Options\Type\File';
					break;
				case 'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Type\Select':
					$option['renderer']='Magebay\Marketplace\Block\Product\CustomOpt\Options\Type\Select';
					break;
				case 'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Type\Date':
					$option['renderer']='Magebay\Marketplace\Block\Product\CustomOpt\Options\Type\Date';
					break;					
			}
            $this->addChild($option['name'] . '_option_type', $option['renderer']);
        }

        return parent::_prepareLayout();
    }	
	
    /**
     * Get Product
     *
     * @return Product
     */
    public function getProduct()
    {
		return $this->_coreRegistry->registry('current_product');
    }
	
    /**
     * @return \Magento\Framework\DataObject[]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getProOptionValues()
    {
        $optionsArr = $this->getProduct()->getOptions();
        if ($optionsArr == null) {
            $optionsArr = [];
        }
		$this->__processGetValue($optionsArr);
        return $this->_values;
    }
	
	/**
	 * @param array $optionsArr
	 * @return void
	 */
	protected function __processGetValue($optionsArr) {
        if (!$this->_values || $this->getIgnoreCaching()) {
            $showPrice = $this->getCanReadPrice();
            $values = [];
            $scope = (int)$this->_scopeConfig->getValue(
                \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            foreach ($optionsArr as $option) {
                /* @var $option \Magento\Catalog\Model\Product\Option */

                $this->setItemCount($option->getOptionId());

                $value = [];

                $value['id'] = $option->getOptionId();
                $value['item_count'] = $this->getItemCount();
                $value['option_id'] = $option->getOptionId();
                $value['title'] = $option->getTitle();
                $value['type'] = $option->getType();
                $value['is_require'] = $option->getIsRequire();
                $value['sort_order'] = $option->getSortOrder();
                $value['can_edit_price'] = $this->getCanEditPrice();

                if (false) {
                    $value['checkboxScopeTitle'] = $this->getOptCheckboxScopeHtml(
                        $option->getOptionId(),
                        'title',
                        is_null($option->getStoreTitle())
                    );
                    $value['scopeTitleDisabled'] = is_null($option->getStoreTitle()) ? 'disabled' : null;
                }

                if ($option->getGroupByType() == \Magento\Catalog\Model\Product\Option::OPTION_GROUP_SELECT) {
                    $i = 0;
                    $itemCount = 0;
                    foreach ($option->getValues() as $_value) {
                        /* @var $_value \Magento\Catalog\Model\Product\Option\Value */
                        $value['optionValues'][$i] = [
                            'item_count' => max($itemCount, $_value->getOptionTypeId()),
                            'option_id' => $_value->getOptionId(),
                            'option_type_id' => $_value->getOptionTypeId(),
                            'title' => $_value->getTitle(),
                            'price' => $showPrice ? $this->getOptPriceValue(
                                $_value->getPrice(),
                                $_value->getPriceType()
                            ) : '',
                            'price_type' => $showPrice ? $_value->getPriceType() : 0,
                            'sku' => $_value->getSku(),
                            'sort_order' => $_value->getSortOrder(),
                        ];

                        if (false) {
                            $value['optionValues'][$i]['checkboxScopeTitle'] = $this->getOptCheckboxScopeHtml(
                                $_value->getOptionId(),
                                'title',
                                is_null($_value->getStoreTitle()),
                                $_value->getOptionTypeId()
                            );
                            $value['optionValues'][$i]['scopeTitleDisabled'] = is_null(
                                $_value->getStoreTitle()
                            ) ? 'disabled' : null;
                            if ($scope == \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE) {
                                $value['optionValues'][$i]['checkboxScopePrice'] = $this->getOptCheckboxScopeHtml(
                                    $_value->getOptionId(),
                                    'price',
                                    is_null($_value->getstorePrice()),
                                    $_value->getOptionTypeId(),
                                    ['$(this).up(1).previous()']
                                );
                                $value['optionValues'][$i]['scopePriceDisabled'] = is_null(
                                    $_value->getStorePrice()
                                ) ? 'disabled' : null;
                            }
                        }
                        $i++;
                    }
                } else {
                    $value['price'] = $showPrice ? $this->getOptPriceValue(
                        $option->getPrice(),
                        $option->getPriceType()
                    ) : '';
                    $value['price_type'] = $option->getPriceType();
                    $value['sku'] = $option->getSku();
                    $value['max_characters'] = $option->getMaxCharacters();
                    $value['file_extension'] = $option->getFileExtension();
                    $value['image_size_x'] = $option->getImageSizeX();
                    $value['image_size_y'] = $option->getImageSizeY();
                    if (false
                        && $scope == \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE
                    ) {
                        $value['checkboxScopePrice'] = $this->getOptCheckboxScopeHtml(
                            $option->getOptionId(),
                            'price',
                            is_null($option->getStorePrice())
                        );
                        $value['scopePriceDisabled'] = is_null($option->getStorePrice()) ? 'disabled' : null;
                    }
                }
                $values[] = new \Magento\Framework\DataObject($value);
            }
            $this->_values = $values;
        }	
	}
	
    /**
     * Retrieve html templates for different types of product custom options
     *
     * @return string
     */
    public function getOptionsTemplatesHtml()
    {				
        $canEditPrice = $this->getCanEditPrice();
        $canReadPrice = $this->getCanReadPrice();
        
		$this->getChildBlock('select_option_type')->setCanReadPrice($canReadPrice)->setCanEditPrice($canEditPrice);		

        $this->getChildBlock('file_option_type')->setCanReadPrice($canReadPrice)->setCanEditPrice($canEditPrice);

        $this->getChildBlock('date_option_type')->setCanReadPrice($canReadPrice)->setCanEditPrice($canEditPrice);

        $this->getChildBlock('text_option_type')->setCanReadPrice($canReadPrice)->setCanEditPrice($canEditPrice);

        $templates = $this->getChildHtml(
            'text_option_type'
        ) . "\n" . $this->getChildHtml(
            'file_option_type'
        ) . "\n" . $this->getChildHtml(
            'select_option_type'
        ) . "\n" . $this->getChildHtml(
            'date_option_type'
        );

        return $templates;
    }
	
    /**
     * Check block is readonly
     *
     * @return bool
     */
    public function checkReadonly()
    {
        return $this->getProduct()->getOptionsReadonly();
    }	
	
    /**
     * Retrieve options field name prefix
     *
     * @return string
     */
    public function getOptFieldName()
    {
        return 'product[options]';
    }
	
    /**
     * Retrieve options field id prefix
     *
     * @return string
     */
    public function getOptFieldId()
    {
        return 'product_option';
    }	
	
    /**
     * @return mixed
     */
    public function getTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setData(
            [
                'id' => $this->getOptFieldId() . '_<%- data.id %>_type',
                'class' => 'select select-product-option-type required-option-select',
            ]
        )->setName(
            $this->getOptFieldName() . '[<%- data.id %>][type]'
        )->setOptions(
            $this->_optionType->toOptionArray()
        );

        return $select->getHtml();
    }
    /**
     * @return int
     */
    public function getItemCount()
    {
        return $this->_itemCount;
    }

    /**
     * @param int $itemCount
     * @return $this
     */
    public function setItemCount($itemCount)
    {
        $this->_itemCount = max($this->_itemCount, $itemCount);
        return $this;
    }
    /**
     * @param Product $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->_productInstance = $product;
        return $this;
    }
    /**
     * Retrieve html of scope checkbox
     *
     * @param string $id
     * @param string $name
     * @param boolean $checked
     * @param string $select_id
     * @param array $containers
     * @return string
     */
    public function getOptCheckboxScopeHtml($id, $name, $checked = true, $select_id = '-1', array $containers = [])
    {
        $checkedHtml = '';
        if ($checked) {
            $checkedHtml = ' checked="checked"';
        }
        $selectNameHtml = '';
        $selectIdHtml = '';
        if ($select_id != '-1') {
            $selectNameHtml = '[values][' . $select_id . ']';
            $selectIdHtml = 'select_' . $select_id . '_';
        }
        $containers[] = '$(this).up(1)';
        $containers = implode(',', $containers);
        $localId = $this->getOptFieldId() . '_' . $id . '_' . $selectIdHtml . $name . '_use_default';
        $localName = "options_use_default[" . $id . "]" . $selectNameHtml . "[" . $name . "]";
        $useDefault =
            '<div class="field-service">'
            . '<input type="checkbox" class="use-default-control"'
            . ' name="' . $localName . '"' . 'id="' . $localId . '"'
            . ' value=""'
            . $checkedHtml
            . ' onchange="toggleSeveralValueElements(this, [' . $containers . ']);" '
            . ' />'
            . '<label for="' . $localId . '" class="use-default">'
            . '<span class="use-default-label">' . __('Use Default') . '</span></label></div>';

        return $useDefault;
    }
    /**
     * @param float $value
     * @param string $type
     * @return string
     */
    public function getOptPriceValue($value, $type)
    {
        if ($type == 'percent') {
            return number_format($value, 2, null, '');
        } elseif ($type == 'fixed') {
            return number_format($value, 2, null, '');
        }
    }
	
    /**
     * @return mixed
     */
    public function getOptRequireSelectHtml()
    {
        $select = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setData(
            ['id' => $this->getOptFieldId() . '_<%- data.id %>_is_require', 'class' => 'select']
        )->setName(
            $this->getOptFieldName() . '[<%- data.id %>][is_require]'
        )->setOptions(
            $this->_configYesNo->toOptionArray()
        );

        return $select->getHtml();
    }	
}