<?php
namespace Magebay\Bookingsystem\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Fieldset;

class Tab extends AbstractModifier
{

    const CUSTOM_TAB_INDEX = 'bk_custom_tab';
    const CUSTOM_TAB_CONTENT = 'content';

    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var array
     */
    protected $meta = [];
    protected $_backendTemplate;
    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder,
        \Magento\Backend\Block\Template $backendTemplate
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->urlBuilder = $urlBuilder;
        $this->_backendTemplate = $backendTemplate;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        $this->addCustomTab();

        return $this->meta;
    }

    protected function addCustomTab()
    {
        $this->meta = array_merge_recursive(
            $this->meta,
            [
                static::CUSTOM_TAB_INDEX => $this->getTabConfig(),
            ]
        );
    }

    protected function getTabConfig()
    {
		$blockTemplate = $this->_backendTemplate;
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Booking System'),
                        'componentType' => Fieldset::NAME,
                        'dataScope' => '',
                        'provider' => static::FORM_NAME . '.product_form_data_source',
                        'ns' => static::FORM_NAME,
                        'collapsible' => true,
                    ],
                ],
            ],
            'children' => [
                static::CUSTOM_TAB_CONTENT => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => null,
                                'formElement' => Container::NAME,
                                'componentType' => Container::NAME,
                                'template' => 'ui/form/components/complex',
                                'content' => $blockTemplate->getLayout()->createBlock('Magebay\Bookingsystem\Block\Marketplace\Tab')->setTemplate('Magebay_Bookingsystem::marketplace/mk_edit21.phtml')->toHtml(),
                                'sortOrder' => 10,
                            ],
                        ],
                    ],
                    'children' => [],
                ],
            ],
        ];
    }
}