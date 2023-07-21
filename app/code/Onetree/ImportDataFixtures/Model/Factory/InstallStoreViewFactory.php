<?php
/**
 * Created by PhpStorm.
 * User: juancarlosc
 * Date: 7/29/18
 * Time: 11:11
 */

namespace Onetree\ImportDataFixtures\Model\Factory;

/**
 * Class InstallStoreViewFactory
 * @package Onetree\ImportDataFixtures\Model\Factory
 */
class InstallStoreViewFactory extends InstallAbstractFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Onetree\ImportDataFixtures\Model\Converter\WebsiteConverter
     */
    private $websiteConverter;
    /**
     * @var \Onetree\ImportDataFixtures\Model\Converter\StoreGroupConverter
     */
    private $storeGroupConverter;

    /**
     * InstallDataBlock constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Onetree\ImportDataFixtures\Model\Converter\WebsiteConverter $websiteConverter
     * @param \Onetree\ImportDataFixtures\Model\Converter\StoreGroupConverter $storeGroupConverter
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Onetree\ImportDataFixtures\Model\Converter\WebsiteConverter $websiteConverter,
        \Onetree\ImportDataFixtures\Model\Converter\StoreGroupConverter $storeGroupConverter
    )
    {
        $this->objectManager = $objectManager;
        $this->websiteConverter = $websiteConverter;
        $this->storeGroupConverter = $storeGroupConverter;
    }

    /**
     * @return \Onetree\ImportDataFixtures\Model\Fixture\FixtureInterface
     */
    public function createInstall()
    {
        $fixture = $this->objectManager->get('Onetree\ImportDataFixtures\Model\Fixture\StoreViewFixture');
        $fixture->addConverter($this->websiteConverter);
        $fixture->addConverter($this->storeGroupConverter);
        return $fixture;
    }
}