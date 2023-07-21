<?php
/**
 * Created by PhpStorm.
 * User: juancarlosc
 * Date: 7/29/18
 * Time: 21:43
 */

namespace Onetree\ImportDataFixtures\Model\Converter;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

/**
 * This class find the widget {{media url="folder/media.jpg"}} and upload the media file
 * Data values are required
 * $data = [
 *      'column' => 'content',
 *      'current_module' => ''
 * ]
 *
 * Class WidgetMediaConverter
 * @package Onetree\ImportDataFixtures\Model\Converter
 */
class WidgetMediaConverter extends AbstractConverter implements ConverterInterface
{
    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    private $storeFactory;
    /**
     * @var \Magento\Store\Model\ResourceModel\Store
     */
    private $storeResourceModel;
    /**
     * @var \Magento\Store\Model\StoreRepository
     */
    private $storeRepository;
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $io;
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;
    /**
     * @var \Onetree\ImportDataFixtures\Logger\Logger
     */
    private $logger;
    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    private $fixtureManager;

    /**
     * WidgetMediaConverter constructor.
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Store\Model\ResourceModel\Store $storeResourceModel
     * @param \Magento\Store\Model\StoreRepository $storeRepository
     * @param \Magento\Framework\Filesystem\Io\File $io
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Onetree\ImportDataFixtures\Logger\Logger $logger
     * @param array $data
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Store\Model\ResourceModel\Store $storeResourceModel,
        \Magento\Store\Model\StoreRepository $storeRepository,
        \Magento\Framework\Filesystem\Io\File $io,
        \Magento\Framework\Filesystem $filesystem,
        \Onetree\ImportDataFixtures\Logger\Logger $logger,
        array $data = [
            self::KEY_COLUMN => 'content'
        ]
    )
    {
        parent::__construct($data);

        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->storeFactory = $storeFactory;
        $this->storeResourceModel = $storeResourceModel;
        $this->storeRepository = $storeRepository;
        $this->io = $io;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
    }

    /**
     * @param array $row
     * @return array
     */
    public function convert($row)
    {
        $content = $row[$this->getData(self::KEY_COLUMN)];
        if (preg_match_all('/{{media\surl=[^}}]*}}/i', $content, $result)) {
            $result = (isset($result[0])) ? $result[0] : [];
            foreach ($result as $media) {
                preg_match('/url="([^"]*)"/i', $media, $path);
                $this->uploadImage($path);
            }
        }

        return $row;
    }

    /**
     * @param $path
     */
    private function uploadImage($path)
    {
        try {
            $pathImage = $path[1];

            $currentModule = $this->getData(self::KEY_CURRENT_MODULE);
            $imageFile = $this->fixtureManager->getFixture("$currentModule::fixtures/media/" . $pathImage);
            if (!is_file($imageFile)) {
                return;
            }

            $imageFileParts = pathinfo($pathImage);

            /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
            $mediaDirectory = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $absolutePath = $mediaDirectory->getAbsolutePath($imageFileParts['dirname']);
            $fileToUpload = $absolutePath . DIRECTORY_SEPARATOR . $imageFileParts['basename'];

            // mkdir directories if doesn't exists
            $this->io->mkdir($absolutePath, 0775);

            copy($imageFile, $fileToUpload);
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage(), $e->getTrace());
        }
    }
}
