<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Product\Configurable\Steps;

use Magento\Eav\Model\Entity\Attribute;

class Bulk extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /** @var Image */
    protected $image;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var Config
     */
    private $catalogProductMediaConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Image $image
     * @param \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Image $image,
        \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        parent::__construct($context);
        $this->image = $image;
        $this->productFactory = $productFactory;
        $this->catalogProductMediaConfig = $catalogProductMediaConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        return __('Bulk Images &amp; Price');
    }

    /**
     * @return string
     */
    public function getProItemNoImageUrl()
    {
        return $this->image->getDefaultPlaceholderUrl('thumbnail');
    }

    /**
     * Get image types data
     *
     * @return array
     */
    public function getProConfigImageTypes()
    {
        $imageTypes = [];
        foreach ($this->catalogProductMediaConfig->getMediaAttributeCodes() as $attributeCode) {
            /* @var $attribute Attribute */
            $imageTypes[$attributeCode] = [
                'code' => $attributeCode,
                'value' => '',
                'label' => $attributeCode,
                'scope' => '',
                'name' => $attributeCode,
            ];
        }
        return $imageTypes;
    }

    /**
     * @return array
     */
    public function getProConfigMediaAttributes()
    {
        static $simple;
        if (empty($simple)) {
            $simple = $this->productFactory->create()->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)->getMediaAttributes();
        }
        return $simple;
    }
}
