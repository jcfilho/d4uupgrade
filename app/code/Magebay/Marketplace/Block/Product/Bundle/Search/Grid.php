<?php

namespace Magebay\Marketplace\Block\Product\Bundle\Search;

class Grid extends \Magebay\Marketplace\Block\Product\Widget\Grid\Extended
{
    /**
     * Bundle data
     *
     * @var \Magento\Bundle\Helper\Data
     */
    protected $_bundleData = null;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Framework\Code\NameBuilder $nameBuilder
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Bundle\Helper\Data $bundleData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Math\Random $mathRandom,
		\Magento\Framework\Data\Form\FormKey $formKey,
		\Magento\Framework\Code\NameBuilder $nameBuilder,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Bundle\Helper\Data $bundleData,
        array $data = []
    ) {
        $this->_bundleData = $bundleData;
        $this->_productFactory = $productFactory;
        parent::__construct($context, $mathRandom, $formKey, $nameBuilder, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('bundle_selection_search_grid');
        $this->setRowClickCallback('bSelection.productGridRowClick.bind(bSelection)');
        $this->setCheckboxCheckCallback('bSelection.productGridCheckboxCheck.bind(bSelection)');
        $this->setRowInitCallback('bSelection.productGridRowInit.bind(bSelection)');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
    }

    /**
     * Prepare grid filter buttons
     *
     * @return void
     */
    protected function _prepareFilterButtons()
    {
        $this->getChildBlock(
            'reset_filter_button'
        )->setData(
            'onclick',
            $this->getJsObjectName() . '.resetFilter(bSelection.gridUpdateCallback)'
        );
        $this->getChildBlock(
            'search_button'
        )->setData(
            'onclick',
            $this->getJsObjectName() . '.doFilter(bSelection.gridUpdateCallback)'
        );
    }

    /**
     * Initialize grid before rendering
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->setId($this->getId() . '_' . $this->getIndex());
        return parent::_beforeToHtml();
    }

    /**
     * Apply sorting and filtering to collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_productFactory->create()->getCollection()->setOrder(
            'id'
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'price'
        )->addAttributeToSelect(
            'attribute_set_id'
        )->addAttributeToFilter(
            'entity_id',
            ['nin' => $this->_getSelectedProducts()]
        )->addAttributeToFilter(
            'type_id',
            ['in' => $this->getAllowedSelectionTypes()]
        )->addFilterByRequiredOptions()->addStoreFilter(
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );

        if ($this->getFirstShow()) {
            $collection->addIdFilter('-1');
            $this->setEmptyText(__('What are you looking for?'));
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Initialize grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'index' => 'entity_id',
                'renderer' => 'Magebay\Marketplace\Block\Product\Widget\Grid\Renderer\Checkbox',
                'type' => 'skip-list'
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Product'),
                'index' => 'name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'name col-name'
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'width' => '80px',
                'index' => 'sku',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'sku col-sku'
            ]
        );
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'align' => 'center',
                'type' => 'currency',
                'index' => 'price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * Retrieve grid reload url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'marketplace/product_bundle/grid',
            ['index' => $this->getIndex(), 'productss' => implode(',', $this->_getProducts())]
        );
    }

    /**
     * @return mixed
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost(
            'selected_products',
            explode(',', $this->getRequest()->getParam('productss'))
        );
        return $products;
    }

    /**
     * @return array
     */
    protected function _getProducts()
    {
        if ($products = $this->getRequest()->getPost('products', null)) {
            return $products;
        } else {
            if ($productss = $this->getRequest()->getParam('productss', null)) {
                return explode(',', $productss);
            } else {
                return [];
            }
        }
    }

    /**
     * Retrieve array of allowed product types for bundle selection product
     *
     * @return array
     */
    public function getAllowedSelectionTypes()
    {
        return $this->_bundleData->getAllowedSelectionTypes();
    }
}
