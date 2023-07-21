<?php

namespace Daytours\RegularServices\Model\ResourceModel\Product\Option;

use Magento\Catalog\Model\Product\Option\Value as OptionValue;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class Value extends \Magento\Catalog\Model\ResourceModel\Product\Option\Value
{

    /**
     * @var FormatInterface
     */
    private $localeFormat;

    /**
     * Save option value price data
     *
     * @param AbstractModel $object
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _saveValuePrices(AbstractModel $object)
    {
        $objectPrice = $object->getPrice();
        $objectChildPrice = $object->getChildPrice();
        $priceTable = $this->getTable('catalog_product_option_type_price');
        $formattedPrice = $this->getLocaleFormatter()->getNumber($objectPrice);
        $formattedChildPrice = $this->getLocaleFormatter()->getNumber($objectChildPrice);

        $price = (double)sprintf('%F', $formattedPrice);
        $childPrice = (double)sprintf('%F', $formattedChildPrice);
        $priceType = $object->getPriceType();

        if (isset($objectPrice) && $priceType) {
            //save for store_id = 0
            $select = $this->getConnection()->select()->from(
                $priceTable,
                'option_type_id'
            )->where(
                'option_type_id = ?',
                (int)$object->getId()
            )->where(
                'store_id = ?',
                Store::DEFAULT_STORE_ID
            );
            $optionTypeId = $this->getConnection()->fetchOne($select);

            if ($optionTypeId) {
                if ($object->getStoreId() == '0') {
                    $bind = ['price' => $price, 'price_type' => $priceType, 'child_price' => $childPrice];
                    $where = [
                        'option_type_id = ?' => $optionTypeId,
                        'store_id = ?' => Store::DEFAULT_STORE_ID,
                    ];

                    $this->getConnection()->update($priceTable, $bind, $where);
                }
            } else {
                $bind = [
                    'option_type_id' => (int)$object->getId(),
                    'store_id' => Store::DEFAULT_STORE_ID,
                    'price' => $price,
                    'price_type' => $priceType,
                    'child_price' => $childPrice,
                ];
                $this->getConnection()->insert($priceTable, $bind);
            }
        }

        $scope = (int)$this->_config->getValue(
            Store::XML_PATH_PRICE_SCOPE,
            ScopeInterface::SCOPE_STORE
        );

        if ($scope == Store::PRICE_SCOPE_WEBSITE
            && $priceType
            && isset($objectPrice)
            && $object->getStoreId() != Store::DEFAULT_STORE_ID
        ) {
            $baseCurrency = $this->_config->getValue(
                Currency::XML_PATH_CURRENCY_BASE,
                'default'
            );

            $storeIds = $this->_storeManager->getStore($object->getStoreId())->getWebsite()->getStoreIds();
            if (is_array($storeIds)) {
                foreach ($storeIds as $storeId) {
                    if ($priceType == 'fixed') {
                        $storeCurrency = $this->_storeManager->getStore($storeId)->getBaseCurrencyCode();
                        /** @var $currencyModel Currency */
                        $currencyModel = $this->_currencyFactory->create();
                        $currencyModel->load($baseCurrency);
                        $rate = $currencyModel->getRate($storeCurrency);
                        if (!$rate) {
                            $rate = 1;
                        }
                        $newPrice = $price * $rate;
                        $newChildPrice = $childPrice * $rate;
                    } else {
                        $newPrice = $price;
                        $newChildPrice = $childPrice;
                    }

                    $select = $this->getConnection()->select()->from(
                        $priceTable,
                        'option_type_id'
                    )->where(
                        'option_type_id = ?',
                        (int)$object->getId()
                    )->where(
                        'store_id = ?',
                        (int)$storeId
                    );
                    $optionTypeId = $this->getConnection()->fetchOne($select);

                    if ($optionTypeId) {
                        $bind = ['price' => $newPrice, 'price_type' => $priceType, 'child_price' => $newChildPrice];
                        $where = ['option_type_id = ?' => (int)$optionTypeId, 'store_id = ?' => (int)$storeId];

                        $this->getConnection()->update($priceTable, $bind, $where);
                    } else {
                        $bind = [
                            'option_type_id' => (int)$object->getId(),
                            'store_id' => (int)$storeId,
                            'price' => $newPrice,
                            'price_type' => $priceType,
                            'child_price' => $newChildPrice,
                        ];

                        $this->getConnection()->insert($priceTable, $bind);
                    }
                }
            }
        } else {
            if ($scope == Store::PRICE_SCOPE_WEBSITE
                && !isset($objectPrice)
                && !$priceType
            ) {
                $storeIds = $this->_storeManager->getStore($object->getStoreId())->getWebsite()->getStoreIds();
                foreach ($storeIds as $storeId) {
                    $where = [
                        'option_type_id = ?' => (int)$object->getId(),
                        'store_id = ?' => $storeId,
                    ];
                    $this->getConnection()->delete($priceTable, $where);
                }
            }
        }
    }

    /**
     * Duplicate product options value
     *
     * @param OptionValue $object
     * @param int $oldOptionId
     * @param int $newOptionId
     * @return OptionValue
     */
    public function duplicate(OptionValue $object, $oldOptionId, $newOptionId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable())->where('option_id = ?', $oldOptionId);
        $valueData = $connection->fetchAll($select);

        $valueCond = [];

        foreach ($valueData as $data) {
            $optionTypeId = $data[$this->getIdFieldName()];
            unset($data[$this->getIdFieldName()]);
            $data['option_id'] = $newOptionId;

            $connection->insert($this->getMainTable(), $data);
            $valueCond[$optionTypeId] = $connection->lastInsertId($this->getMainTable());
        }

        unset($valueData);

        foreach ($valueCond as $oldTypeId => $newTypeId) {
            // price
            $priceTable = $this->getTable('catalog_product_option_type_price');
            $columns = [new \Zend_Db_Expr($newTypeId), 'store_id', 'price', 'price_type', 'child_price'];

            $select = $connection->select()->from(
                $priceTable,
                []
            )->where(
                'option_type_id = ?',
                $oldTypeId
            )->columns(
                $columns
            );
            $insertSelect = $connection->insertFromSelect(
                $select,
                $priceTable,
                ['option_type_id', 'store_id', 'price', 'price_type', 'child_price']
            );
            $connection->query($insertSelect);

            // title
            $titleTable = $this->getTable('catalog_product_option_type_title');
            $columns = [new \Zend_Db_Expr($newTypeId), 'store_id', 'title'];

            $select = $this->getConnection()->select()->from(
                $titleTable,
                []
            )->where(
                'option_type_id = ?',
                $oldTypeId
            )->columns(
                $columns
            );
            $insertSelect = $connection->insertFromSelect(
                $select,
                $titleTable,
                ['option_type_id', 'store_id', 'title']
            );
            $connection->query($insertSelect);
        }

        return $object;
    }

    /**
     * Get FormatInterface to convert price from string to number format
     *
     * @return FormatInterface
     * @deprecated 101.0.8
     */
    private function getLocaleFormatter()
    {
        if ($this->localeFormat === null) {
            $this->localeFormat = ObjectManager::getInstance()
                ->get(FormatInterface::class);
        }
        return $this->localeFormat;
    }
}
