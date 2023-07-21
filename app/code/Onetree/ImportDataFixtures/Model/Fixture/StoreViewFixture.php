<?php

namespace Onetree\ImportDataFixtures\Model\Fixture;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class StoreViewFixture
 * @package Onetree\ImportDataFixtures\Model\Fixture
 */
class StoreViewFixture extends AbstractFixture
{
    const KEY_FIXTURE_ID = 'store_id';
    const KEY_IDENTIFIER = 'code';
    const KEY_WEBSITE_ID = 'website_id';
    const KEY_GROUP_ID = 'group_id';
    const KEY_NAME = 'name';
    const KEY_SORT_ORDER = 'sort_order';
    const KEY_IS_ACTIVE = 'is_active';

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    private $storeFactory;
    /**
     * @var \Magento\Store\Model\StoreRepository
     */
    private $storeRepository;
    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $filterManager;
    /**
     * @var \Magento\Store\Model\GroupRepository
     */
    private $groupRepository;
    /**
     * @var \Magento\Store\Model\ResourceModel\Store
     */
    private $storeResourcemodel;
    /**
     * @var \Magento\Store\Model\ResourceModel\Group
     */
    private $groupResourceModel;
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * StoreViewFixture constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Store\Model\StoreRepository $storeRepository
     * @param \Magento\Store\Model\ResourceModel\Store $storeResourceModel
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Store\Model\GroupRepository $groupRepository
     * @param \Magento\Store\Model\ResourceModel\Group $groupResourceModel
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Store\Model\StoreRepository $storeRepository,
        \Magento\Store\Model\ResourceModel\Store $storeResourceModel,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Store\Model\GroupRepository $groupRepository,
        \Magento\Store\Model\ResourceModel\Group $groupResourceModel
    ) {
        parent::__construct($objectManager);

        $this->storeFactory = $storeFactory;
        $this->eventManager = $eventManager;
        $this->storeRepository = $storeRepository;
        $this->filterManager = $filterManager;
        $this->groupRepository = $groupRepository;
        $this->storeResourcemodel = $storeResourceModel;
        $this->groupResourceModel = $groupResourceModel;
    }

    /**
     * @param $data
     * @return void
     */
    protected function installData($data)
    {
        $this->logger->debug("[StoreViewFixture][InstallData]", $data);

        try {
            $this->proccessStoreSave($data);
        } catch (LocalizedException $e) {
            $this->logger->addError($e->getMessage(), $e->getTrace());
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage(), $e->getTrace());
        }
    }

    /**
     * @param array $postData
     * @throws LocalizedException
     */
    private function proccessStoreSave($postData)
    {
        $eventName = 'store_edit';
        /** @var \Magento\Store\Model\Store $storeModel */
        $storeModel = $this->storeFactory->create();
        $postData['name'] = $this->filterManager->removeTags($postData['name']);
        try {
            if (isset($postData[self::KEY_FIXTURE_ID]) && !empty($postData[self::KEY_FIXTURE_ID])) {
                $storeModel = $this->storeRepository->getById($postData[self::KEY_FIXTURE_ID]);
                $postData['store_id'] = $storeModel->getId();
            } else if (isset($postData[self::KEY_IDENTIFIER])) {
                $storeModel = $this->storeRepository->get($postData[self::KEY_IDENTIFIER]);
                $postData['store_id'] = $storeModel->getId();
            }
        } catch(NoSuchEntityException $e) {
            // if there isn't a store model then create a new store model to save
            $this->logger->debug($e->getMessage());
        }

        // the converter website return an array of the websites, so we need to update
        if (is_array($postData['website_id'])) {
            $postData['website_id'] = $postData['website_id'][0];
        }
        if (is_array($postData['group_id'])) {
            $postData['group_id'] = $postData['group_id'][0];
        }

        $storeModel->setData($postData);
        if (!isset($postData['store_id']) || $postData['store_id'] == '') {
            $storeModel->setId(null);
            $eventName = 'store_add';
        }
        $groupModel = $this->groupRepository->get($storeModel->getGroupId());
        $storeModel->setWebsiteId($groupModel->getWebsiteId());
        if (!$storeModel->isActive() && $storeModel->isDefault()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The default store cannot be disabled')
            );
        }

        $this->storeResourcemodel->save($storeModel);
        $this->objectManager->get(\Magento\Store\Model\StoreManager::class)->reinitStores();
        $this->eventManager->dispatch($eventName, ['store' => $storeModel]);

        // check if store view will be the new default store view for the group
        $this->checkIfStoreIsDefaultStore($postData, $groupModel, $storeModel->getId());
    }

    /**
     * @param $postData
     * @param $groupModel
     * @param $storeId
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function checkIfStoreIsDefaultStore($postData, $groupModel, $storeId)
    {
        // check if the store view is the default store view
        if (isset($postData['default_store_id']) && $postData['default_store_id']) {
            if (!$this->isSelectedDefaultStoreActive($postData['default_store_id'], $groupModel)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('An inactive store view cannot be saved as default store view')
                );
            }
            $groupModel->setDefaultStoreId($storeId);
            $this->groupResourceModel->save($groupModel);
            $this->eventManager->dispatch('store_group_save', ['group' => $groupModel]);
        }
    }

    /**
     * Verify if selected default store is active
     *
     * @param $defaultStoreId
     * @param \Magento\Store\Model\Group $groupModel
     * @return bool
     */
    private function isSelectedDefaultStoreActive($defaultStoreId, \Magento\Store\Model\Group $groupModel)
    {
        if (isset($defaultStoreId)) {
            if (!empty($groupModel->getStores()[$defaultStoreId]) && !$groupModel->getStores()[$defaultStoreId]->isActive()
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param \Magento\Store\Model\Store $storeModel
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function saveStoreModel(\Magento\Store\Model\Store $storeModel) {
        $eventName = 'store_edit';
        if ($storeModel->getId()) {
            $eventName = 'store_add';
        }
        $this->storeResourcemodel->save($storeModel);
        $this->objectManager->get(\Magento\Store\Model\StoreManager::class)->reinitStores();
        $this->eventManager->dispatch($eventName, ['store' => $storeModel]);
    }
}