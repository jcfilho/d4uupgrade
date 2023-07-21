<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Daytours\Wishlist\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Wishlist\Model\Item\Option;
use Magento\Wishlist\Model\Item\OptionFactory;
use Magento\Wishlist\Model\ResourceModel\Item\Option\CollectionFactory;
use Magento\Catalog\Model\Product\Exception as ProductException;

/**
 * Wishlist item model
 *
 * @method int getWishlistId()
 * @method \Magento\Wishlist\Model\Item setWishlistId(int $value)
 * @method int getProductId()
 * @method \Magento\Wishlist\Model\Item setProductId(int $value)
 * @method int getStoreId()
 * @method \Magento\Wishlist\Model\Item setStoreId(int $value)
 * @method string getAddedAt()
 * @method \Magento\Wishlist\Model\Item setAddedAt(string $value)
 * @method string getDescription()
 * @method \Magento\Wishlist\Model\Item setDescription(string $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 100.0.2
 */
class Item extends \Magento\Wishlist\Model\Item
{
    /**
     * Custom path to download attached file
     * @var string
     */
    protected $_customOptionDownloadUrl = 'wishlist/index/downloadCustomOption';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'wishlist_item';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getItem() in this case
     *
     * @var string
     */
    protected $_eventObject = 'item';

    /**
     * Item options array
     *
     * @var Option[]
     */
    protected $_options = [];

    /**
     * Item options by code cache
     *
     * @var array
     */
    protected $_optionsByCode = [];

    /**
     * Not Represent options
     *
     * @var string[]
     */
    protected $_notRepresentOptions = ['info_buyRequest'];

    /**
     * Flag stating that options were successfully saved
     *
     * @var bool|null
     */
    protected $_flagOptionsSaved = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Url
     */
    protected $_catalogUrl;

    /**
     * @var OptionFactory
     */
    protected $_wishlistOptFactory;

    /**
     * @var CollectionFactory
     */
    protected $_wishlOptionCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $productTypeConfig;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrl
     * @param OptionFactory $wishlistOptFactory
     * @param CollectionFactory $wishlOptionCollectionFactory
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        OptionFactory $wishlistOptFactory,
        CollectionFactory $wishlOptionCollectionFactory,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->productTypeConfig = $productTypeConfig;
        $this->_storeManager = $storeManager;
        $this->_date = $date;
        $this->_catalogUrl = $catalogUrl;
        $this->_wishlistOptFactory = $wishlistOptFactory;
        $this->_wishlOptionCollectionFactory = $wishlOptionCollectionFactory;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        parent::__construct($context,
        $registry,
        $storeManager,
        $date,
        $catalogUrl,
        $wishlistOptFactory,
        $wishlOptionCollectionFactory,
        $productTypeConfig,
        $productRepository,
        $resource = null,
        $resourceCollection = null,
        $data,
        $serializer = null

        );
        $this->productRepository = $productRepository;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Wishlist\Model\ResourceModel\Item::class);
    }


    /**
     * Retrieve item product instance
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        $product = $this->_getData('product');
        if ($product === null) {
            if (!$this->getProductId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Cannot specify product.'));
            }
            try {
                $product = $this->productRepository->getById($this->getProductId(), false, $this->getStoreId(), true);
            } catch (NoSuchEntityException $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Cannot specify product.'), $e);
            }
            $this->setData('product', $product);
        }

        /**
         * Reset product final price because it related to custom options
         */
        $product->setFinalPrice(null);

        $additionalInfo = $product->getCustomOptions();
        $merge = array_merge($additionalInfo);

        $product->setCustomOptions($merge);
        return $product;
    }

}
