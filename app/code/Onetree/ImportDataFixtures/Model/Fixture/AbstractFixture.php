<?php
/**
 * Created by PhpStorm.
 * User: juancarlosc
 * Date: 7/29/18
 * Time: 11:19
 */

namespace Onetree\ImportDataFixtures\Model\Fixture;

use Magento\Framework\Exception\NoSuchEntityException;

abstract class AbstractFixture implements FixtureInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    protected $fixtureManager;
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * This variable is the current module, eg: Vendor_ModuleName
     * @var string
     */
    protected $currentModule = '';
    /**
     * @var \Onetree\ImportDataFixtures\Logger\Logger
     */
    protected $logger;
    /**
     * @var \Magento\Framework\Setup\SampleData\Context
     */
    protected $sampleDataContext;
    /**
     * @var \Onetree\ImportDataFixtures\Model\Converter\ConverterInterface[]
     */
    protected $converters = [];

    /**
     * AbstractFixture constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->objectManager = $objectManager;
        $this->logger = $objectManager->get('Onetree\ImportDataFixtures\Logger\Logger');

        $sampleDataContext = $objectManager->get('Magento\Framework\Setup\SampleData\Context');
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
    }

    /**
     * @param array $fixtures
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function install(array $fixtures)
    {
        foreach ($fixtures as $fileName) {
            // get the current module and update the variable $currentModule
            $this->updateCurrentModule($fileName);

            // check if exists de file
            $fileName = $this->fixtureManager->getFixture($fileName);
            if (!file_exists($fileName)) {
                continue;
            }

            // get data by row
            $rows = $this->csvReader->getData($fileName);
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $row = $data;
                $row = $this->applyConverters($row);
                $this->installData($row);
            }
        }
    }

    /**
     * the argument represent a row of data
     *
     * @param $data
     * @return bool
     */
    abstract protected function installData($data);

    /**
     * @return string
     */
    public function getCurrentModule()
    {
        return $this->currentModule;
    }

    private function updateCurrentModule($fileName)
    {
        list($moduleName, $filePath) = \Magento\Framework\View\Asset\Repository::extractModule(
            $this->fixtureManager->normalizePath($fileName)
        );
        $this->logger->debug("The current module is $moduleName");
        $this->currentModule = $moduleName;
    }

    /**
     * @param \Onetree\ImportDataFixtures\Model\Converter\ConverterInterface $converter
     * @throws NoSuchEntityException
     */
    public function addConverter($converter)
    {
        if (!$converter instanceof \Onetree\ImportDataFixtures\Model\Converter\AbstractConverter) {
            throw new NoSuchEntityException();
        }
        $instance = get_class($converter);
        if (!array_key_exists($instance, $this->converters)) {
            $this->converters[$instance] = $converter;
        }

    }

    /**
     * @param array $row
     * @return array
     */
    protected function applyConverters(array $row)
    {
        foreach ($this->converters as $converter) {
            $converter->setData(\Onetree\ImportDataFixtures\Model\Converter\AbstractConverter::KEY_CURRENT_MODULE, $this->getCurrentModule());
            $row = $converter->convert($row);
        }

        return $row;
    }
}