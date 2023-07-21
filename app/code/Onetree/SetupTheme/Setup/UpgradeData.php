<?php

namespace Onetree\SetupTheme\Setup;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Onetree\ImportDataFixtures\Model\Factory\InstallBlockFactory
     */
    private $installBlockFactory;
    /**
     * @var \Onetree\ImportDataFixtures\Model\Factory\InstallPageFactory
     */
    private $installPageFactory;
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    private $resourceConfig;
    /**
     * @var \Onetree\SetupTheme\Helper\Deploy
     */
    private $pubDeployer;
    /**
     * @var \Onetree\ImportDataFixtures\Model\Factory\InstallCategoryFactory
     */
    private $installCategoryFactory;
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @var \Onetree\SetupTheme\Model\Website
     */
    private $websiteInstall;
    /**
     * @var \Onetree\SetupTheme\Model\Store
     */
    private $storeGroupInstall;
    /**
     * @var \Onetree\SetupTheme\Model\Storeview
     */
    private $storeviewInstall;
    /**
     * @var \Onetree\SetupTheme\Model\DesignConfig
     */
    private $designConfigInstall;

    /**
     * UpgradeData constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Onetree\ImportDataFixtures\Model\Factory\InstallBlockFactory $installBlockFactory
     * @param \Onetree\ImportDataFixtures\Model\Factory\InstallPageFactory $installPageFactory
     * @param \Onetree\ImportDataFixtures\Model\Factory\InstallCategoryFactory $installCategoryFactory
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param \Onetree\SetupTheme\Helper\Deploy $pubDeployer
     * @param \Onetree\SetupTheme\Helper\Data $helper
     * @param EavSetupFactory $eavSetupFactory
     * @param \Onetree\SetupTheme\Model\Website $websiteInstall
     * @param \Onetree\SetupTheme\Model\Store $storeGroupInstall
     * @param \Onetree\SetupTheme\Model\Storeview $storeviewInstall
     * @param \Onetree\SetupTheme\Model\DesignConfig $designConfigInstall
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Onetree\ImportDataFixtures\Model\Factory\InstallBlockFactory $installBlockFactory,
        \Onetree\ImportDataFixtures\Model\Factory\InstallPageFactory $installPageFactory,
        \Onetree\ImportDataFixtures\Model\Factory\InstallCategoryFactory
        $installCategoryFactory, \Magento\Framework\App\State $state,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Onetree\SetupTheme\Helper\Deploy $pubDeployer,
        \Onetree\SetupTheme\Helper\Data $helper,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Onetree\SetupTheme\Model\Website $websiteInstall,
        \Onetree\SetupTheme\Model\Store $storeGroupInstall,
        \Onetree\SetupTheme\Model\Storeview $storeviewInstall,
        \Onetree\SetupTheme\Model\DesignConfig $designConfigInstall
    )
    {
        $this->logger = $logger;
        $this->installBlockFactory = $installBlockFactory;
        $this->installPageFactory = $installPageFactory;
        $this->state = $state;
        $this->resourceConfig = $resourceConfig;
        $this->pubDeployer = $pubDeployer;
        $this->installCategoryFactory = $installCategoryFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->websiteInstall = $websiteInstall;
        $this->storeGroupInstall = $storeGroupInstall;
        $this->storeviewInstall = $storeviewInstall;
        $this->designConfigInstall = $designConfigInstall;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * save or update cms for destinations
         */
        if (version_compare($context->getVersion(), '0.1.1') < 0) {
            try {
                $this->state->getAreaCode();
            } catch (LocalizedException $e) {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            }
            $installPage = $this->installBlockFactory->createInstall();
            $installPage->install(['Onetree_SetupTheme::fixtures/csv/blocks/cms_block_0.1.1.csv']);
        }

        /**
         * save or update cms for destinations - about
         */
        if (version_compare($context->getVersion(), '0.1.2') < 0) {
            try {
                $this->state->getAreaCode();
            } catch (LocalizedException $e) {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            }
            $installPage = $this->installBlockFactory->createInstall();
            $installPage->install(['Onetree_SetupTheme::fixtures/csv/blocks/cms_block_0.1.2.csv']);
        }

        /**
         * save or update cms for regions
         */
        if (version_compare($context->getVersion(), '0.1.3') < 0) {
            try {
                $this->state->getAreaCode();
            } catch (LocalizedException $e) {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            }
            $installPage = $this->installBlockFactory->createInstall();
            $installPage->install(['Onetree_SetupTheme::fixtures/csv/blocks/cms_block_0.1.3.csv']);
        }

        /**
         * save or update cms for regions - about
         */
        if (version_compare($context->getVersion(), '0.1.4') < 0) {
            try {
                $this->state->getAreaCode();
            } catch (LocalizedException $e) {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            }
            $installPage = $this->installBlockFactory->createInstall();
            $installPage->install(['Onetree_SetupTheme::fixtures/csv/blocks/cms_block_0.1.4.csv']);
        }

        /**
         * save or update cms for tours
         */
        if (version_compare($context->getVersion(), '0.1.5') < 0) {
            try {
                $this->state->getAreaCode();
            } catch (LocalizedException $e) {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            }
            $installPage = $this->installBlockFactory->createInstall();
            $installPage->install(['Onetree_SetupTheme::fixtures/csv/blocks/cms_block_0.1.5.csv']);
        }

        /**
         * save or update cms for tours - about
         */
        if (version_compare($context->getVersion(), '0.1.6') < 0) {
            try {
                $this->state->getAreaCode();
            } catch (LocalizedException $e) {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            }
            $installPage = $this->installBlockFactory->createInstall();
            $installPage->install(['Onetree_SetupTheme::fixtures/csv/blocks/cms_block_0.1.6.csv']);
        }

        /**
         * save or update cms for destinations - banners
         */
        if (version_compare($context->getVersion(), '0.1.7') < 0) {
            try {
                $this->state->getAreaCode();
            } catch (LocalizedException $e) {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            }
            $installPage = $this->installBlockFactory->createInstall();
            $installPage->install(['Onetree_SetupTheme::fixtures/csv/blocks/cms_block_0.1.7.csv']);
        }

        /**
         * save or update cms for destinations - about - countries - arg, uru, etc
         */
        if (version_compare($context->getVersion(), '0.1.8') < 0) {
            try {
                $this->state->getAreaCode();
            } catch (LocalizedException $e) {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            }
            $installPage = $this->installBlockFactory->createInstall();
            $installPage->install(['Onetree_SetupTheme::fixtures/csv/blocks/cms_block_0.1.8.csv']);
        }

        /**
         * save or update cms for destinations - countries - banners - arg, uru, etc
         */
        if (version_compare($context->getVersion(), '0.1.9') < 0) {
            try {
                $this->state->getAreaCode();
            } catch (LocalizedException $e) {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            }
            $installPage = $this->installBlockFactory->createInstall();
            $installPage->install(['Onetree_SetupTheme::fixtures/csv/blocks/cms_block_0.1.9.csv']);
        }

        /**
         * save or update cms for destinations - about - cities - arg, uru, etc
         */
        if (version_compare($context->getVersion(), '0.1.10') < 0) {
            try {
                $this->state->getAreaCode();
            } catch (LocalizedException $e) {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            }
            $installPage = $this->installBlockFactory->createInstall();
            $installPage->install(['Onetree_SetupTheme::fixtures/csv/blocks/cms_block_0.1.10.csv']);
        }

        /**
         * save or update cms for destinations - cities - banners - arg, uru, etc
         */
        if (version_compare($context->getVersion(), '0.1.11') < 0) {
            try {
                $this->state->getAreaCode();
            } catch (LocalizedException $e) {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            }
            $installPage = $this->installBlockFactory->createInstall();
            $installPage->install(['Onetree_SetupTheme::fixtures/csv/blocks/cms_block_0.1.11.csv']);
        }

        /**
         * save or update cms for destinations - cities - banners - arg, uru, etc
         */
        if (version_compare($context->getVersion(), '0.1.12') < 0) {
            try {
                $this->state->getAreaCode();
            } catch (LocalizedException $e) {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            }
            $installPage = $this->installPageFactory->createInstall();
            $installPage->install(['Onetree_SetupTheme::fixtures/csv/pages/cms_page_0.0.1.csv']);
        }

        /**
         * save or update cms for destinations - cities - banners - arg, uru, etc
         */
        if (version_compare($context->getVersion(), '0.1.13') < 0) {
            try {
                $this->state->getAreaCode();
            } catch (LocalizedException $e) {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            }
            $installPage = $this->installPageFactory->createInstall();
            $installPage->install(['Onetree_SetupTheme::fixtures/csv/blocks/cms_block_0.1.12.csv']);
        }

        /**
         * save or update cms for destinations - cities - banners - arg, uru, etc
         */
        if (version_compare($context->getVersion(), '0.1.14') < 0) {
            try {
                $this->state->getAreaCode();
            } catch (LocalizedException $e) {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            }
            $installPage = $this->installBlockFactory->createInstall();
            $installPage->install(['Onetree_SetupTheme::fixtures/csv/blocks/cms_block_0.1.14.csv']);
        }

        /**
         * save or update cms for destinations - cities - banners - arg, uru, etc
         */
        if (version_compare($context->getVersion(), '0.1.15') < 0) {
            try {
                $this->state->getAreaCode();
            } catch (LocalizedException $e) {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
            }
            $installPage = $this->installBlockFactory->createInstall();
            $installPage->install(['Onetree_SetupTheme::fixtures/csv/blocks/cms_block_0.1.15.csv']);
        }

        if (version_compare($context->getVersion(), '0.1.16') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $entityTypeId = $eavSetup->getEntityTypeId('catalog_product');

            $attributeSetTransfer = $eavSetup->getAttributeSet($entityTypeId, 'Transfer', 'attribute_set_name');

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'limit_going',
                [
                    'type' => 'text',
                    'label' => 'Limit Going',
                    'input' => 'text',
                    'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'default' => '',
                    'apply_to' => '',
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'limit_rountrip',
                [
                    'type' => 'text',
                    'label' => 'Limit Rountrip',
                    'input' => 'text',
                    'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'default' => '',
                    'apply_to' => '',
                ]
            );

            $eavSetup->addAttributeToSet(
                $entityTypeId,
                $attributeSetTransfer,
                'General',
                $eavSetup->getAttributeId($entityTypeId, 'limit_going')
            );
            $eavSetup->addAttributeToSet(
                $entityTypeId,
                $attributeSetTransfer,
                'General',
                $eavSetup->getAttributeId($entityTypeId, 'limit_rountrip')
            );


        }

        if (version_compare($context->getVersion(), '0.1.18') < 0) {
            try {
                $this->state->emulateAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL, [$this->websiteInstall, 'install'], [['Onetree_SetupTheme::fixtures/csv/websites/websites_install_0.1.18.csv']]);
            } catch (\Exception $e) {
            }

            try {
                $this->state->emulateAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL, [$this->storeGroupInstall, 'install'], [['Onetree_SetupTheme::fixtures/csv/stores/stores_install_0.1.18.csv']]);
            } catch (\Exception $e) {
            }

            try {
                $this->state->emulateAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL, [$this->storeviewInstall, 'install'], [['Onetree_SetupTheme::fixtures/csv/stores_view/stores_view_install_0.1.18.csv']]);
            } catch (\Exception $e) {
            }

            try {
                $this->state->emulateAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL, [$this->designConfigInstall, 'install'], [['Onetree_SetupTheme::fixtures/csv/design-config/design-config_0.1.18.csv']]);
            } catch (\Exception $e) {
            }
        }

        $setup->endSetup();
    }
}