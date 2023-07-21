<?php
namespace Magebay\Bookingsystem\Model\Import\Type;
class Booking extends \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
{
    /**
     * Delimiter before product option value.
     */
    const BEFORE_OPTION_VALUE_DELIMITER = ';';

    /**
     * Pair value separator.
     */
    const PAIR_VALUE_SEPARATOR = '=';

    /**
     * Dynamic value.
     */
    const VALUE_DYNAMIC = 'dynamic';

    /**
     * Fixed value.
     */
    const VALUE_FIXED = 'fixed';

    /**
     * Not fixed dynamic attribute.
     */
    const NOT_FIXED_DYNAMIC_ATTRIBUTE = 'price_view';

    /**
     * Selection price type fixed.
     */
    const SELECTION_PRICE_TYPE_FIXED = 0;

    /**
     * Selection price type percent.
     */
    const SELECTION_PRICE_TYPE_PERCENT = 1;

    /**
     * Instance of database adapter.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * Instance of application resource.
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * Array of cached options.
     *
     * @var array
     */
    protected $_cachedOptions = [];

    /**
     * Array of cached skus.
     *
     * @var array
     */
    protected $_cachedSkus = [];

    /**
     * Mapping array between cached skus and products.
     *
     * @var array
     */
    protected $_cachedSkuToProducts = [];

    /**
     * Array of queries selecting cached options.
     *
     * @var array
     */
    protected $_cachedOptionSelectQuery = [];

    public function isRowValid(array $rowData, $rowNum, $isNewProduct = true)
    {
        // Ваша логика
        return parent::isRowValid($rowData, $rowNum, $isNewProduct);
    }

    // your code could be here
}