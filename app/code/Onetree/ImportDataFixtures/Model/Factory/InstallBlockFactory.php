<?php

namespace Onetree\ImportDataFixtures\Model\Factory;

class InstallBlockFactory extends InstallAbstractFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Onetree\ImportDataFixtures\Model\Converter\StoreConverter
     */
    private $storeConverter;

    /**
     * InstallDataBlock constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Onetree\ImportDataFixtures\Model\Converter\StoreConverter $storeConverter
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Onetree\ImportDataFixtures\Model\Converter\StoreConverter $storeConverter
    )
    {
        $this->objectManager = $objectManager;
        $this->storeConverter = $storeConverter;
    }

    /**
     * @return \Onetree\ImportDataFixtures\Model\Fixture\FixtureInterface
     */
    public function createInstall()
    {
        $fixture = $this->objectManager->get('Onetree\ImportDataFixtures\Model\Fixture\BlockFixture');
        $fixture->addConverter($this->storeConverter);
        return $fixture;
    }
}