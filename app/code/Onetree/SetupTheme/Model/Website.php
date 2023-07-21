<?php

namespace Onetree\SetupTheme\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

class Website
{
    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    private $fixtureManager;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $Factory;
    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $filterManager;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Magento\Store\Model\ResourceModel\Website
     */
    private $websiteResourceModel;
    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    private $websiteFactory;
    /**
     * @var \Magento\Store\Model\WebsiteRepository
     */
    private $websiteRepository;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Website constructor.
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\ResourceModel\Website $websiteResourceModel
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Store\Model\WebsiteRepository $websiteRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\ResourceModel\Website $websiteResourceModel,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Store\Model\WebsiteRepository $websiteRepository,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();

        $this->filterManager = $filterManager;
        $this->objectManager = $objectManager;
        $this->websiteResourceModel = $websiteResourceModel;
        $this->websiteFactory = $websiteFactory;
        $this->websiteRepository = $websiteRepository;
        $this->logger = $logger;
    }

    /**
     * @param array $fixtures
     * @throws \Exception
     */
    public function install(array $fixtures)
    {
        foreach ($fixtures as $fileName) {
            $fileName = $this->fixtureManager->getFixture($fileName);
            if (!file_exists($fileName)) {
                continue;
            }

            $rows = $this->csvReader->getData($fileName);
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $row = $data;

                $websites['website'] = $row;
                $this->processWebsiteSave($websites);
            }
        }
    }

    /**
     * Process Website model save
     *
     * @param $postData
     * @return array
     */
    private function processWebsiteSave($postData)
    {
        $postData['website']['name'] = $this->filterManager->removeTags($postData['website']['name']);
        /** @var \Magento\Store\Model\Website $websiteModel */
        $websiteModel = $this->websiteFactory->create();
        if ($postData['website']['website_id']) {
            $this->websiteResourceModel->load($websiteModel, $postData['website']['website_id']);
        } else if ($postData['website']['code']) {
            $this->websiteResourceModel->load($websiteModel, $postData['website']['code'], 'code');
            $postData['website']['website_id'] = ($websiteModel->getId()) ? $websiteModel->getId() : '';
        }

        $websiteModel->setData($postData['website']);
        if ($postData['website']['website_id'] == '') {
            $websiteModel->setId(null);
        }

        $this->websiteResourceModel->save($websiteModel);

        return $postData;
    }
}
