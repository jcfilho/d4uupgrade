<?php

namespace Onetree\SetupTheme\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

class Storeview
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
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    /**
     * @var \Magento\Store\Model\ResourceModel\Group
     */
    private $groupResourceModel;
    /**
     * @var \Magento\Store\Model\GroupFactory
     */
    private $groupFactory;
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
     * Storeview constructor.
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\ResourceModel\Website $websiteResourceModel
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Store\Model\WebsiteRepository $websiteRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\ResourceModel\Group $groupResourceModel
     * @param \Magento\Store\Model\GroupFactory $groupFactory
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Store\Model\ResourceModel\Store $storeResourceModel
     * @param \Magento\Store\Model\StoreRepository $storeRepository
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\ResourceModel\Website $websiteResourceModel,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Store\Model\WebsiteRepository $websiteRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\ResourceModel\Group $groupResourceModel,
        \Magento\Store\Model\GroupFactory $groupFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Store\Model\ResourceModel\Store $storeResourceModel,
        \Magento\Store\Model\StoreRepository $storeRepository
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
        $this->eventManager = $eventManager;
        $this->groupResourceModel = $groupResourceModel;
        $this->groupFactory = $groupFactory;
        $this->storeFactory = $storeFactory;
        $this->storeResourceModel = $storeResourceModel;
        $this->storeRepository = $storeRepository;
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

                $storeView['store'] = $row;
                $this->processStoreSave($storeView);
            }
        }
    }

    /**
     * Process Store model save
     *
     * @param array $postData
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return array
     */
    private function processStoreSave($postData)
    {
        $groupModel = $this->groupFactory->create();
        // if group_code exists then load and set the group_id in the array
        if (isset($postData['store']['group_code'])) {
            $this->groupResourceModel->load($groupModel, $postData['store']['group_code'], 'code');
            $postData['store']['group_id'] = $groupModel->getId();
        }

        $eventName = 'store_edit';
        /** @var \Magento\Store\Model\Store $storeModel */
        $storeModel = $this->storeFactory->create();
        $postData['store']['name'] = $this->filterManager->removeTags($postData['store']['name']);
        if ($postData['store']['store_id']) {
            $this->storeResourceModel->load($storeModel, $postData['store']['store_id']);
        } else if ($postData['store']['code']) {
            $this->storeResourceModel->load($storeModel, $postData['store']['code'], 'code');
            $postData['store']['store_id'] = ($storeModel->getId()) ? $storeModel->getId() : '';
        }

        $storeModel->setData($postData['store']);
        if ($postData['store']['store_id'] == '') {
            $storeModel->setId(null);
            $eventName = 'store_add';
        }

        $storeModel->setWebsiteId($groupModel->getWebsiteId());
        if ($storeModel->isDefault() && !$storeModel->isActive()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The default store cannot be disabled')
            );
        }

        $this->storeResourceModel->save($storeModel);
        $this->objectManager->get(\Magento\Store\Model\StoreManager::class)->reinitStores();
        $this->eventManager->dispatch($eventName, ['store' => $storeModel]);

        return $postData;
    }
}
