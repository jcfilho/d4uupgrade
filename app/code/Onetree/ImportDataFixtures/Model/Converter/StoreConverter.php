<?php
/**
 * Created by PhpStorm.
 * User: juancarlosc
 * Date: 7/29/18
 * Time: 21:43
 */

namespace Onetree\ImportDataFixtures\Model\Converter;

/**
 * This class convert store code to store id
 * Data values are required
 * $data = [
 *      'column' => 'store_id',
 *      'current_module' => ''
 * ]
 *
 * Class StoreConverter
 * @package Onetree\ImportDataFixtures\Model\Converter
 */
class StoreConverter extends AbstractConverter implements ConverterInterface
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
     * @var \Onetree\ImportDataFixtures\Logger\Logger
     */
    private $logger;

    /**
     * StoreConverter constructor.
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Store\Model\ResourceModel\Store $storeResourceModel
     * @param \Magento\Store\Model\StoreRepository $storeRepository
     * @param \Onetree\ImportDataFixtures\Logger\Logger $logger
     * @param array $data
     */
    public function __construct(
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Store\Model\ResourceModel\Store $storeResourceModel,
        \Magento\Store\Model\StoreRepository $storeRepository,
        \Onetree\ImportDataFixtures\Logger\Logger $logger,
        array $data = [
            self::KEY_COLUMN => 'store_id'
        ]
    )
    {
        parent::__construct($data);

        $this->storeFactory = $storeFactory;
        $this->storeResourceModel = $storeResourceModel;
        $this->storeRepository = $storeRepository;
        $this->logger = $logger;
    }

    /**
     * @param array $row
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Store\Model\StoreIsInactiveException
     */
    public function convert($row)
    {

        $value = $row[$this->getData(self::KEY_COLUMN)];
        $storesCode = explode(',', $value);
        $storeIds = [];
        foreach ($storesCode as $storeCode) {
            if (is_string($storeCode)) {
                $store = $this->storeRepository->getActiveStoreById($storeCode);
                $storeIds[] = $store->getId();
            }
        }

        if (empty($storeIds)) {
            $storeIds = 0;
        } else if (count($storeIds) == 1) {
            $storeIds = $storeIds[0];
        }

        $row[$this->getData(self::KEY_COLUMN)] = $storeIds;

        return $row;
    }
}
