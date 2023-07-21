<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Product\Downloadable;

class Links extends \Magento\Framework\View\Element\Template
{
    /**
     * Purchased Separately Attribute cache
     *
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected $_purchasedSeparatelyAttribute = null;

    /**
     * Downloadable file
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $_downloadableFile = null;

    /**
     * Core file storage database
     *
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $_coreFileStorageDb = null;	

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Magento\Downloadable\Model\Link
     */
    protected $_link;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;
	
	protected $_coreRegistry;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Downloadable\Helper\File $downloadableFile 
     * @param \Magento\Downloadable\Model\Link $link	 
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,	
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
		\Magento\Framework\Registry $coreRegistry,
        \Magento\Downloadable\Helper\File $downloadableFile,
        \Magento\Downloadable\Model\Link $link,		
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;        
        $this->_downloadableFile = $downloadableFile;
        $this->_link = $link;
        $this->_attributeFactory = $attributeFactory;
		$this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setCanEditPrice(true);
        $this->setCanReadPrice(true);
    }

    /**
     * Get product that is being edited
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
		return $this->_coreRegistry->registry('current_product');
    }

    /**
     * Get Links can be purchased separately value for current product
     *
     * @return bool
     */
    public function checkProductLinksCanBePurchasedSeparately()
    {
        return (bool) $this->getProduct()->getData('links_purchased_separately');
    }

    /**
     * Retrieve Add button HTML
     *
     * @return string
     */
    public function addLinkButtonHtml()
    {
        $addButton = $this->getLayout()->createBlock(
            'Magebay\Marketplace\Block\Product\Widget\Button'
        )->setData(
            [
                'label' => __('Add New Link'),
                'id' => 'add_link_item',
                'class' => 'action-add',
                'data_attribute' => ['action' => 'add-link'],
            ]
        );
        return $addButton->toHtml();
    }

    /**
     * Retrieve default links title
     *
     * @return string
     */
    public function getProDownloadLinksTitle()
    {
        return $this->getProduct()->getId() &&
            $this->getProduct()->getTypeId() ==
            'downloadable' ? $this->getProduct()->getLinksTitle() : $this->_scopeConfig->getValue(
                \Magento\Downloadable\Model\Link::XML_PATH_LINKS_TITLE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * Check exists defined links title
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function checkUsedDefault()
    {
        return $this->getProduct()->getAttributeDefaultValue('links_title') === false;
    }

    /**
     * Return true if price in website scope
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function checkIsPriceWebsiteScope()
    {
        $scope = (int)$this->_scopeConfig->getValue(
            \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($scope == \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE) {
            return true;
        }
        return false;
    }
	
	/**
	 * @return array
	 */
	protected function processGetDownloadLinkData() {
		$linkArr = array();
        $links = $this->getProduct()->getTypeInstance()->getLinks($this->getProduct());
        $priceWebsiteScope = $this->checkIsPriceWebsiteScope();
        $fileHelper = $this->_downloadableFile;
        foreach ($links as $item) {
            $tmpLinkItem = [
                'link_id' => $item->getId(),
                'title' => $this->escapeHtml($item->getTitle()),
                'price' => number_format($item->getPrice(), 2, null, ''),
                'number_of_downloads' => $item->getNumberOfDownloads(),
                'is_shareable' => $item->getIsShareable(),
                'link_url' => $item->getLinkUrl(),
                'link_type' => $item->getLinkType(),
                'sample_file' => $item->getSampleFile(),
                'sample_url' => $item->getSampleUrl(),
                'sample_type' => $item->getSampleType(),
                'sort_order' => $item->getSortOrder(),
            ];

            $linkFile = $item->getLinkFile();
            if ($linkFile) {
                $file = $fileHelper->getFilePath($this->_link->getBasePath(), $linkFile);

                $fileExist = $fileHelper->ensureFileInFilesystem($file);

                if ($fileExist) {
                    $name = '<a href="' . $this->getUrl(
                        'marketplace/product_downloadable_edit/link',
                        ['id' => $item->getId(), 'type' => 'link', '_secure' => true]
                    ) . '">' . $fileHelper->getFileFromPathFile(
                        $linkFile
                    ) . '</a>';
                    $tmpLinkItem['file_save'] = [
                        [
                            'file' => $linkFile,
                            'name' => $name,
                            'size' => $fileHelper->getFileSize($file),
                            'status' => 'old',
                        ],
                    ];
                }
            }

            $sampleFile = $item->getSampleFile();
            if ($sampleFile) {
                $file = $fileHelper->getFilePath($this->_link->getBaseSamplePath(), $sampleFile);

                $fileExist = $fileHelper->ensureFileInFilesystem($file);

                if ($fileExist) {
                    $name = '<a href="' . $this->getUrl(
                        'marketplace/product_downloadable_edit/link',
                        ['id' => $item->getId(), 'type' => 'sample', '_secure' => true]
                    ) . '">' . $fileHelper->getFileFromPathFile(
                        $sampleFile
                    ) . '</a>';
                    $tmpLinkItem['sample_file_save'] = [
                        [
                            'file' => $item->getSampleFile(),
                            'name' => $name,
                            'size' => $fileHelper->getFileSize($file),
                            'status' => 'old',
                        ],
                    ];
                }
            }

            if ($item->getNumberOfDownloads() == '0') {
                $tmpLinkItem['is_unlimited'] = ' checked="checked"';
            }
            if ($this->getProduct()->getStoreId() && $item->getStoreTitle()) {
                $tmpLinkItem['store_title'] = $item->getStoreTitle();
            }
            if ($this->getProduct()->getStoreId() && $priceWebsiteScope) {
                $tmpLinkItem['website_price'] = $item->getWebsitePrice();
            }
            $linkArr[] = new \Magento\Framework\DataObject($tmpLinkItem);
        }
		return $linkArr;
	}
    /**
     * @return array
     */
    public function getProDownloadLinkData()
    {
        $linkArr = [];
        if ($this->getProduct()->getTypeId() !== \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            return $linkArr;
        }
		$linkArr = $this->processGetDownloadLinkData();
        return $linkArr;
    }

    /**
     * Retrieve max downloads value from config
     *
     * @return int
     */
    public function getMaxDownloads()
    {
        return $this->_scopeConfig->getValue(
            \Magento\Downloadable\Model\Link::XML_PATH_DEFAULT_DOWNLOADS_NUMBER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
	
    /**
     * @return string
     */
    public function getProLinkConfigJson($type = 'links')
    {
		$config = new \Magento\Framework\DataObject();
        $config->setUrl($this->getUrl('marketplace/product_downloadable_file/upload',['type' => $type, '_secure' => true]));
        $config->setParams(['form_key' => $this->getFormKey()]);
        $config->setFileField($type);
        $config->setFilters(['all' => ['label' => __('All Files'), 'files' => ['*.*']]]);
        $config->setReplaceBrowseWithRemove(true);
        $config->setWidth('32');
        $config->setHideUploadButton(true);
        return $this->_jsonEncoder->encode($config->getData());
    }

    /**
     * @param null|string|bool|int|\Magento\Store\Model\Store $storeId $storeId
     * @return string
     */
    public function getBaseCurrencyCode($storeId)
    {
        return $this->_storeManager->getStore($storeId)->getBaseCurrencyCode();
    }

    /**
     * @param null|string|bool|int|\Magento\Store\Model\Store $storeId $storeId
     * @return string
     */
    public function getBaseCurrencySymbol($storeId)
    {
        return $this->_storeManager->getStore($storeId)->getBaseCurrency()->getCurrencySymbol();
    }
	
	public function getStoreId(){
		$_product=$this->getProduct();
		return (int)$_product->getData('store_id');
	}
}