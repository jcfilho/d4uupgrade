<?php

namespace Magebay\Bookingsystem\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magebay\Bookingsystem\Model\Product\Type\Booking as BookingType;
/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
   /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
		$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
		$attributes = [
				'cost',
				'price',
				'special_price',
				'tax_class_id'
			];
		foreach ($attributes as $attributeCode) {
			$relatedProductTypes = explode(
				',',
				$eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode, 'apply_to')
			);
			if (!in_array(BookingType::TYPE_CODE, $relatedProductTypes)) {
				$relatedProductTypes[] = BookingType::TYPE_CODE;
				$eavSetup->updateAttribute(
					\Magento\Catalog\Model\Product::ENTITY,
					$attributeCode,
					'apply_to',
					implode(',', $relatedProductTypes)
				);
			}
		}
    }
}
