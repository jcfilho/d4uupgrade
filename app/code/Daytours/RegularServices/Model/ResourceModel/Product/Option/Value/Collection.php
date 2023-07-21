<?php

namespace Daytours\RegularServices\Model\ResourceModel\Product\Option\Value;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection
{

    /**
     * Add titles to result
     *
     * @param int $storeId
     * @return $this
     */
    public function addTitlesToResult($storeId)
    {
        $connection = $this->getConnection();
        $optionTypePriceTable = $this->getTable('catalog_product_option_type_price');
        $optionTitleTable = $this->getTable('catalog_product_option_type_title');
        $priceExpr = $connection->getCheckSql(
            'store_value_price.price IS NULL',
            'default_value_price.price',
            'store_value_price.price'
        );
        $childPriceExpr = $connection->getCheckSql(
            'store_value_price.child_price IS NULL',
            'default_value_price.child_price',
            'store_value_price.child_price'
        );
        $priceTypeExpr = $connection->getCheckSql(
            'store_value_price.price_type IS NULL',
            'default_value_price.price_type',
            'store_value_price.price_type'
        );
        $titleExpr = $connection->getCheckSql(
            'store_value_title.title IS NULL',
            'default_value_title.title',
            'store_value_title.title'
        );
        $joinExprDefaultPrice = 'default_value_price.option_type_id = main_table.option_type_id AND ' .
            $connection->quoteInto('default_value_price.store_id = ?', \Magento\Store\Model\Store::DEFAULT_STORE_ID);

        $joinExprStorePrice = 'store_value_price.option_type_id = main_table.option_type_id AND ' .
            $connection->quoteInto('store_value_price.store_id = ?', $storeId);

        $joinExprTitle = 'store_value_title.option_type_id = main_table.option_type_id AND ' . $connection->quoteInto(
            'store_value_title.store_id = ?',
            $storeId
        );

        $this->getSelect()->joinLeft(
            ['default_value_price' => $optionTypePriceTable],
            $joinExprDefaultPrice,
            ['default_price' => 'price', 'default_price_type' => 'price_type']
        )->joinLeft(
            ['store_value_price' => $optionTypePriceTable],
            $joinExprStorePrice,
            [
                'store_price' => 'price',
                'store_price_type' => 'price_type',
                'price' => $priceExpr,
                'child_price' => $childPriceExpr,
                'price_type' => $priceTypeExpr
            ]
        )->join(
            ['default_value_title' => $optionTitleTable],
            'default_value_title.option_type_id = main_table.option_type_id',
            ['default_title' => 'title']
        )->joinLeft(
            ['store_value_title' => $optionTitleTable],
            $joinExprTitle,
            ['store_title' => 'title', 'title' => $titleExpr]
        )->where(
            'default_value_title.store_id = ?',
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );

        return $this;
    }

    /**
     * Add price to result
     *
     * @param int $storeId
     * @return $this
     */
    public function addPriceToResult($storeId)
    {
        $optionTypeTable = $this->getTable('catalog_product_option_type_price');
        $priceExpr = $this->getConnection()->getCheckSql(
            'store_value_price.price IS NULL',
            'default_value_price.price',
            'store_value_price.price'
        );
        $childPriceExpr = $this->getConnection()->getCheckSql(
            'store_value_price.child_price IS NULL',
            'default_value_price.child_price',
            'store_value_price.child_price'
        );
        $priceTypeExpr = $this->getConnection()->getCheckSql(
            'store_value_price.price_type IS NULL',
            'default_value_price.price_type',
            'store_value_price.price_type'
        );

        $joinExprDefault = 'default_value_price.option_type_id = main_table.option_type_id AND ' .
            $this->getConnection()->quoteInto(
                'default_value_price.store_id = ?',
                \Magento\Store\Model\Store::DEFAULT_STORE_ID
            );
        $joinExprStore = 'store_value_price.option_type_id = main_table.option_type_id AND ' .
            $this->getConnection()->quoteInto('store_value_price.store_id = ?', $storeId);
        $this->getSelect()->joinLeft(
            ['default_value_price' => $optionTypeTable],
            $joinExprDefault,
            ['default_price' => 'price', 'default_price_type' => 'price_type']
        )->joinLeft(
            ['store_value_price' => $optionTypeTable],
            $joinExprStore,
            [
                'store_price' => 'price',
                'store_price_type' => 'price_type',
                'price' => $priceExpr,
                'child_price' => $childPriceExpr,
                'price_type' => $priceTypeExpr
            ]
        );

        return $this;
    }
}
