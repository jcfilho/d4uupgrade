<?php
/**
 * Created by PhpStorm.
 * User: juancarlosc
 * Date: 7/29/18
 * Time: 21:43
 */

namespace Onetree\ImportDataFixtures\Model\Converter;

/**
 * Class StoreGroupConverter
 * @package Onetree\ImportDataFixtures\Model\Converter
 */
class StoreGroupConverter extends AbstractConverter implements ConverterInterface
{
    /**
     * @var \Magento\Store\Model\GroupFactory
     */
    private $storeGroupFactory;
    /**
     * @var \Magento\Store\Model\ResourceModel\Group
     */
    private $storeGroupResourceModel;
    /**
     * @var \Magento\Store\Model\GroupRepository
     */
    private $storeGroupRepository;
    /**
     * @var \Onetree\ImportDataFixtures\Logger\Logger
     */
    private $logger;

    /**
     * StoreGroupConverter constructor.
     * @param \Magento\Store\Model\GroupFactory $storeGroupFactory
     * @param \Magento\Store\Model\ResourceModel\Group $storeGroupResourceModel
     * @param \Magento\Store\Model\GroupRepository $storeGroupRepository
     * @param \Onetree\ImportDataFixtures\Logger\Logger $logger
     * @param array $data
     */
    public function __construct(
        \Magento\Store\Model\GroupFactory $storeGroupFactory,
        \Magento\Store\Model\ResourceModel\Group $storeGroupResourceModel,
        \Magento\Store\Model\GroupRepository $storeGroupRepository,
        \Onetree\ImportDataFixtures\Logger\Logger $logger,
        array $data = [
            self::KEY_COLUMN => 'group_id'
        ]
    )
    {
        parent::__construct($data);
        $this->storeGroupFactory = $storeGroupFactory;
        $this->storeGroupResourceModel = $storeGroupResourceModel;
        $this->storeGroupRepository = $storeGroupRepository;
        $this->logger = $logger;
    }

    /**
     * @param array $row
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function convert($row)
    {
        $value = $row[$this->getData(self::KEY_COLUMN)];
        $storeGroupsCode = explode(',', $value);
        $storeGroupIds = [];
        foreach ($storeGroupsCode as $storeGroupCode) {
            $storeGroupModel = $this->storeGroupFactory->create();
            if (is_numeric($storeGroupCode)) {
                $storeGroupModel = $this->storeGroupRepository->get($storeGroupCode);
            } else {
                foreach ($this->storeGroupRepository->getList() as $storeGroupItem) {
                    if ($storeGroupItem->getCode() == $storeGroupCode) {
                        $storeGroupModel = $storeGroupItem;
                    }
                }
            }

            if ($storeGroupModel->getId()) {
                $storeGroupIds[] = $storeGroupModel->getId();
            }
        }
        $row[$this->getData(self::KEY_COLUMN)] = $storeGroupIds;

        return $row;
    }
}
