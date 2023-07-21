<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebay\Marketplace\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\UrlInterface;

/**
 * Class SystemDataProvider
 */
class System extends AbstractModifier
{
    const KEY_SUBMIT_URL = 'submit_url';
    const KEY_VALIDATE_URL = 'validate_url';
    const KEY_RELOAD_URL = 'reloadUrl';
    const STR_FIND_VALIDATE = 'catalog/product/validate';
	const STR_REPLACE_VALIDATE = 'marketplace/product/validate';
    const STR_FIND = 'catalog/product/save';
    const STR_REPLACE_SAVE = 'marketplace/product_add/savePost';
    

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var array
     */
	protected $productUrls = [
        self::KEY_SUBMIT_URL => 'marketplace/product_add/savePost',
        self::KEY_VALIDATE_URL => 'marketplace/product/validate',
        self::KEY_RELOAD_URL => 'marketplace/product/reload'
    ];

    /**
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     * @param array $productUrls
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        array $productUrls = []
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->productUrls = array_replace_recursive($this->productUrls, $productUrls);
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $model = $this->locator->getProduct();
        $attributeSetId = $model->getAttributeSetId();

        $parameters = [
            'id' => $model->getId(),
            'type' => $model->getTypeId(),
            'store' => $model->getStoreId(),
        ];
        $actionParameters = array_merge($parameters, ['set' => $attributeSetId]);
        $reloadParameters = array_merge(
            $parameters,
            [
                'popup' => 1,
                'componentJson' => 1,
                'prev_set_id' => $attributeSetId,
                'type' => $this->locator->getProduct()->getTypeId()
            ]
        );

        $submitUrl = $this->urlBuilder->getUrl($this->productUrls[self::KEY_SUBMIT_URL], $actionParameters);
		//$submitUrl = str_replace(self::STR_FIND, self::STR_REPLACE_SAVE , $submitUrl);		
        $validateUrl = $this->urlBuilder->getUrl($this->productUrls[self::KEY_VALIDATE_URL], $actionParameters);
		//$validateUrl = str_replace(self::STR_FIND_VALIDATE, self::STR_REPLACE_VALIDATE , $validateUrl);		
        $reloadUrl = $this->urlBuilder->getUrl($this->productUrls[self::KEY_RELOAD_URL], $reloadParameters);

		$_result = array_replace_recursive(
            $data,
            [
                'config' => [
                    self::KEY_SUBMIT_URL => $submitUrl,
                    self::KEY_VALIDATE_URL => $validateUrl,
                    self::KEY_RELOAD_URL => $reloadUrl,
                ]
            ]
        );
        return $_result;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
