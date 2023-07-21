<?php

namespace Onetree\SetupTheme\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

class Store
{

    const ROOT_CATEGORY_ID = 1;

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
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    private $categoryFactory;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    private $categoryResourceModel;
    /**
     * @var \Magento\Catalog\Model\Category|\Magento\Catalog\Model\CategoryFactory
     */
    private $category;

    /**
     * Website constructor.
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
     * @param \Magento\Catalog\Model\CategoryFactory $category
     * @param \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel
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
        \Magento\Catalog\Model\Category $category,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel
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
        $this->categoryFactory = $categoryFactory;
        $this->categoryResourceModel = $categoryResourceModel;
        $this->category = $category;
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

                $store['group'] = $row;
                $this->processGroupSave($store);
            }
        }
    }

    /**
     * Process StoreGroup model save
     *
     * @param array $postData
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return array
     */
    private function processGroupSave($postData)
    {
        // if website_code exists then load and set the website_id in the array
        if (isset($postData['group']['website_code'])) {
            $website = $this->websiteFactory->create();
            $this->websiteResourceModel->load($website, $postData['group']['website_code'], 'code');
            $postData['group']['website_id'] = $website->getId();
        }

        $postData['group']['name'] = $this->filterManager->removeTags($postData['group']['name']);
        /** @var \Magento\Store\Model\Group $groupModel */
        $groupModel = $this->groupFactory->create();
        if ($postData['group']['group_id']) {
            $this->groupResourceModel->load($groupModel, $postData['group']['group_id']);
            $postData['group']['root_category_id'] = $this->createRootCategyIfIsNecesary($postData);
        } else if ($postData['group']['code']) {
            $this->groupResourceModel->load($groupModel, $postData['group']['code'], 'code');
            $postData['group']['group_id'] = ($groupModel->getId()) ? $groupModel->getId() : '';

            if( empty($postData['group']['group_id']) ){
                if(isset($postData['group']['root_category_id']) && empty($postData['group']['root_category_id'])){
                    $postData['group']['root_category_id'] = $this->createRootCategyIfIsNecesary($postData);
                }
            } else{
                $postData['group']['root_category_id'] = $this->createRootCategyIfIsNecesary($postData);
            }
        } else{
            //If store group exist and nedds to create category
            $postData['group']['root_category_id'] = $this->createRootCategyIfIsNecesary($postData);
        }

        $groupModel->setData($postData['group']);
        if ($postData['group']['group_id'] == '') {
            $groupModel->setId(null);
        }
        if (!$this->isSelectedDefaultStoreActive($postData, $groupModel)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('An inactive store view cannot be saved as default store view')
            );
        }
        $this->groupResourceModel->save($groupModel);

        // dispatch event
        $this->eventManager->dispatch('store_group_save', ['group' => $groupModel]);

        return $postData;
    }

    private function createRootCategyIfIsNecesary($postData){
        if( isset($postData['group']['create_root_category']) && !empty($postData['group']['root_category_name'])){
            //If store group exist and nedds to create category
            return $this->getRootCategory($postData);
        }
    }

    /**
     * Return root category, if not exist it'll create one
     * @param $postData
     * @return mixed
     */
    private function getRootCategory($postData){
        $rootCat = $this->category->load(self::ROOT_CATEGORY_ID);

        $existCategory = $this->categoryFactory->create()->loadByAttribute('name',$postData['group']['root_category_name']);
        if( $existCategory ){
            return $existCategory->getId();
        }else{
            $categoryTmp = $this->categoryFactory->create();
            $categoryTmp->setName($postData['group']['root_category_name']);
            $categoryTmp->setIsActive(true);
            $categoryTmp->setParentId($rootCat->getId());
            $categoryTmp->setPath($rootCat->getPath());

            if( $categoryTmp->save() ){
                return $categoryTmp->getId();
            }
        }
    }

    /**
     * Verify if selected default store is active
     *
     * @param array $postData
     * @param \Magento\Store\Model\Group $groupModel
     * @return bool
     */
    private function isSelectedDefaultStoreActive(array $postData, \Magento\Store\Model\Group $groupModel)
    {
        if (isset($postData['group']['default_store_id']) && !empty($postData['group']['default_store_id'])) {
            $defaultStoreId = $postData['group']['default_store_id'];
            if (!empty($groupModel->getStores()[$defaultStoreId]) && !$groupModel->getStores()[$defaultStoreId]->isActive()
            ) {
                return false;
            }
        }

        return true;
    }
}
