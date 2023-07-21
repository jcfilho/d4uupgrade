<?php
namespace Magebay\Bookingsystem\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Fieldset;

class CustomTab extends AbstractModifier
{

    const CUSTOM_TAB_INDEX = 'custom_tab';
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

    public function modifyMeta(array $meta)
    {
        $blockTemplate = $this->_backendTemplate;
        $meta['test_fieldset_name'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Booking System'),
                        'sortOrder' => 10,
                        'collapsible' => true,
                        'componentType' => 'fieldset'
                    ]
                ]
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
                                'content' => $blockTemplate->getLayout()->createBlock('Magebay\Bookingsystem\Block\Adminhtml\Product\Edit\From')->setTemplate('Magebay_Bookingsystem::catalog/product/bk21/edit.phtml')->toHtml(),
                                'sortOrder' => 10,
                            ],
                        ],
                    ],
                    'children' => [],
                ],
            ],
        ];

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}