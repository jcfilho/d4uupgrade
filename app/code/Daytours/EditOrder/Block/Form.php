<?php

namespace Daytours\EditOrder\Block;

use Magento\Catalog\Block\Product\AbstractProduct;

class Form extends AbstractProduct
{

    /**
     * @var \Daytours\EditOrder\Helper\Option
     */
    protected $optionHelper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Daytours\EditOrder\Helper\Option $optionHelper,
        array $data = []
    )
    {
        $this->optionHelper = $optionHelper;
        parent::__construct($context, $data);
    }

    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    public function setCurrentProduct($product)
    {
        $this->_coreRegistry->unregister('product');
        $this->_coreRegistry->unregister('current_product');
        $this->_coreRegistry->register('product', $product);
        $this->_coreRegistry->register('current_product', $product);
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    public function setCurrentItem($item)
    {
        $this->_coreRegistry->unregister('current_item');
        $this->_coreRegistry->register('current_item', $item);
    }

    public function getCurrentItem()
    {
        return $this->_coreRegistry->registry('current_item');
    }

    public function renderCustomOptions()
    {
        $suffix = "[{$this->getCurrentItem()->getId()}][{$this->getProduct()->getId()}]";
        $wrapperOptionsBlock = $this->_createBlock(
            'Magento\Catalog\Block\Product\View',
            'Daytours_EditOrder::product/view/options/wrapper.phtml',
            'product.info.options.wrapper' . $suffix
        );
        $productOptionsBlock = $this->_createBlock(
            'Magento\Catalog\Block\Product\View\Options',
            'Magento_Catalog::product/view/options.phtml',
            'product.info.options' . $suffix
        );
        $calendarBlock = $this->_createBlock(
            'Magento\Framework\View\Element\Html\Calendar',
            'Magento_Theme::js/calendar.phtml',
            'html_calendar' . $suffix
        );
        $optionsChildren = array(
            'Magento\Catalog\Block\Product\View\Options\Type\DefaultType' => array(
                'Daytours_EditOrder::product/view/options/type/default.phtml',
                'product.info.options.default' . $suffix,
                'default'
            ),
            'Daytours\EditOrder\Block\Product\View\Options\Type\Text' => array(
                'Daytours_EditOrder::product/view/options/type/text.phtml',
                'product.info.options.text' . $suffix,
                'text'
            ),
            'Magento\Catalog\Block\Product\View\Options\Type\File' => array(
                'Daytours_EditOrder::product/view/options/type/file.phtml',
                'product.info.options.file' . $suffix,
                'file'
            ),
            'Daytours\EditOrder\Block\Product\View\Options\Type\Select' => array(
                'Daytours_EditOrder::product/view/options/type/select.phtml',
                'product.info.options.select' . $suffix,
                'select'
            ),
            'Magento\Catalog\Block\Product\View\Options\Type\Date' => array(
                'Daytours_EditOrder::product/view/options/type/date.phtml',
                'product.info.options.date' . $suffix,
                'date'
            ),
        );

        foreach ($optionsChildren as $block => $data) {
            $productOptionsBlock->append(
                $this->_createBlock($block, $data[0], $data[1]),
                $data[2]
            );
        }

        $wrapperOptionsBlock->append($productOptionsBlock, 'product_options');
        $wrapperOptionsBlock->append($calendarBlock, 'html_calendar');

        return $wrapperOptionsBlock->toHtml();
    }

    public function renderAddons()
    {
        return $this->_createBlock(
            'Magebay\Bookingsystem\Block\Booking',
            'Daytours_EditOrder::product/view/booking/addons.phtml'
        )->toHtml();
    }

    protected function _createBlock($block, $template, $name = '')
    {
        return $this->getLayout()
            ->createBlock($block, $name)
            ->setTemplate($template)
            ->setCurrentItem($this->getCurrentItem());
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        $order = $this->getOrder();

        return __(sprintf(
            'The following information is required to complete order #%s. Once you have complete all the requested information you will not be able to edit. If you need help please <a href="%s">contact us</a>',
            $order->getIncrementId(),
            $this->getUrl('contact')
        ));
    }

    /**
     * Retrieve the number of adults and children for the product
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return array
     */
    public function getAdultChildCounts($orderItem)
    {
        $adultChildCounts = array('Adult' => $orderItem->getProductOptions()['info_buyRequest']['qty'], 'Child' => 0);
        $product = $orderItem->getProduct();
        $options = $orderItem->getProductOptions();
        if ($product->getTypeInstance()->hasOptions($product)) {
            foreach ($product->getOptions() as $customOption) {
                if ($customOption->getIsChild()) {
                    $option = $this->optionHelper->getOptionById($options, $customOption->getId());
                    if ($option) {
                        $adultChildCounts['Child'] = $option['value'];
                    }

                }
            }
        }

        return $adultChildCounts;
    }

}
