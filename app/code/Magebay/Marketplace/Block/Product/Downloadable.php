<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Product;

class Downloadable extends \Magento\Framework\View\Element\Template
{
    /**
     * Reference to product objects that is being edited
     *
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product = null;

    /**
     * @var \Magento\Framework\DataObject|null
     */
    protected $_config = null;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

	protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    /**
     * Retrieve product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
		return $this->_coreRegistry->registry('current_product');
    }
	
    /**
     * @return bool
     */
    public function isDownloadable()
    {
        return $this->getProduct()->getTypeId() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE;
    }
}