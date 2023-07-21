<?php

namespace Onetree\SetupTheme\Setup;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 * @package Onetree\SetupTheme\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Onetree\ImportDataFixtures\Model\Factory\InstallStoreViewFactory
     */
    private $installStoreViewFactory;
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * InstallData constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Onetree\ImportDataFixtures\Model\Factory\InstallStoreViewFactory $installStoreViewFactory
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Onetree\ImportDataFixtures\Model\Factory\InstallStoreViewFactory $installStoreViewFactory,
        \Magento\Framework\App\State $state
    )
    {
        $this->logger = $logger;
        $this->installStoreViewFactory = $installStoreViewFactory;
        $this->state = $state;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws LocalizedException
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            $this->state->getAreaCode();
        } catch (LocalizedException $e) {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        }

//        $storeViewInstall = $this->installStoreViewFactory->createInstall();
//        $storeViewInstall->install(['Onetree_SetupTheme::fixtures/csv/stores_view/stores_view_install.csv']);
    }
}