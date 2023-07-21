<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Product\Configurable;

class CreateOptions extends \Magebay\Marketplace\Controller\Product\Account
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory
    ) {
        $this->_attributeFactory = $attributeFactory;
        parent::__construct($context, $customerSession);
    }
	
    /**
     * @return void
     */
    public function execute()
    {
        $options = (array)$this->getRequest()->getParam('options');
        $savedOptions = [];
        foreach ($options as $option) {
            if (isset($option['label']) && isset($option['is_new'])) {
                $attribute = $this->_attributeFactory->create();
                $attribute->load($option['attribute_id']);
                $optionsBefore = $attribute->getSource()->getAllOptions(false);
                $attribute->setOption(
                    [
                        'value' => ['option_0' => [$option['label']]],
                        'order' => ['option_0' => count($optionsBefore) + 1],
                    ]
                );
                $attribute->save();
                $attribute = $this->_attributeFactory->create();
                $attribute->load($option['attribute_id']);
                $optionsAfter = $attribute->getSource()->getAllOptions(false);
                $newOption = array_pop($optionsAfter);
                $savedOptions[$option['id']] = $newOption['value'];
            }
        }		
		$data = $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($savedOptions);
        $this->getResponse()->representJson($data);
    }
}