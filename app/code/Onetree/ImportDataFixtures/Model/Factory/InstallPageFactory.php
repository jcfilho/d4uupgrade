<?php

namespace Onetree\ImportDataFixtures\Model\Factory;

class InstallPageFactory extends InstallAbstractFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * InstallDataBlock constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @return \Onetree\ImportDataFixtures\Model\Fixture\FixtureInterface
     */
    public function createInstall()
    {
        $fixture = $this->objectManager->get('Onetree\ImportDataFixtures\Model\Fixture\PageFixture');
        return $fixture;
    }
}