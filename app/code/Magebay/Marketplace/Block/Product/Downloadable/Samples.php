<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Product\Downloadable;

class Samples extends \Magento\Framework\View\Element\Template
{
	protected $_config;
    /**
     * Downloadable file
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $_downloadableFile = null;

    /**
     * @var \Magento\Downloadable\Model\Sample
     */
    protected $_sampleModel;
    
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;
	
	protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Downloadable\Helper\File $downloadableFile     
     * @param \Magento\Downloadable\Model\Sample $sampleModel  
	 * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
	 * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Downloadable\Helper\File $downloadableFile,        
        \Magento\Downloadable\Model\Sample $sampleModel,
		\Magento\Framework\Json\EncoderInterface $jsonEncoder,
		\Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_downloadableFile = $downloadableFile;        
        $this->_sampleModel = $sampleModel;
		$this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Get model of the product that is being edited
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
		return $this->_coreRegistry->registry('current_product');
    }

    /**
     * Retrieve Add Button HTML
     *
     * @return string
     */
    public function addSamplesButtonHtml()
    {
        $addButton = $this->getLayout()->createBlock(
            'Magebay\Marketplace\Block\Product\Widget\Button'
        )->setData(
            [
                'label' => __('Add New Link'),
                'id' => 'add_sample_item',
                'class' => 'action-add',
                'data_attribute' => ['action' => 'add-sample'],
            ]
        );
        return $addButton->toHtml();
    }

	/**
	 * @return array
	 */
	protected function processGetSampleData() {
		$samplesArr = array();
        $samples = $this->getProduct()->getTypeInstance()->getSamples($this->getProduct());
        $fileHelper = $this->_downloadableFile;
        foreach ($samples as $item) {
            $tmpSampleItem = [
                'sample_id' => $item->getId(),
                'title' => $this->escapeHtml($item->getTitle()),
                'sample_url' => $item->getSampleUrl(),
                'sample_type' => $item->getSampleType(),
                'sort_order' => $item->getSortOrder(),
            ];

            $sampleFile = $item->getSampleFile();
            if ($sampleFile) {
                $file = $fileHelper->getFilePath($this->_sampleModel->getBasePath(), $sampleFile);

                $fileExist = $fileHelper->ensureFileInFilesystem($file);

                if ($fileExist) {
                    $name = '<a href="' . $this->getUrl(
                        'marketplace/product_downloadable_edit/sample',
                        ['id' => $item->getId(), '_secure' => true]
                    ) . '">' . $fileHelper->getFileFromPathFile(
                        $sampleFile
                    ) . '</a>';
                    $tmpSampleItem['file_save'] = [
                        [
                            'file' => $sampleFile,
                            'name' => $name,
                            'size' => $fileHelper->getFileSize($file),
                            'status' => 'old',
                        ],
                    ];
                }
            }

            if ($this->getProduct() && $item->getStoreTitle()) {
                $tmpSampleItem['store_title'] = $item->getStoreTitle();
            }
            $samplesArr[] = new \Magento\Framework\DataObject($tmpSampleItem);
        }
		return $samplesArr;
	}
	
    /**
     * Retrieve samples array
     *
     * @return array
     */
    public function getProSampleData()
    {
        $samplesArr = [];
        if ($this->getProduct()->getTypeId() !== \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            return $samplesArr;
        }
		$samplesArr = $this->processGetSampleData();
        return $samplesArr;
    }

    /**
     * Check exists defined samples title
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function checkUsedDefault()
    {
        return $this->getProduct()->getAttributeDefaultValue('samples_title') === false;
    }

    /**
     * Retrieve Default samples title
     *
     * @return string
     */
    public function getProSamplesTitle()
    {
        return $this->getProduct()->getId()
        && $this->getProduct()->getTypeId() == 'downloadable' ? $this->getProduct()->getSamplesTitle() :
            $this->_scopeConfig->getValue(
                \Magento\Downloadable\Model\Sample::XML_PATH_SAMPLES_TITLE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }
	
    /**
     * Retrieve config json
     *
     * @return string
     */
    public function getProSamplesConfigJson()
    {
        $url = $this->getUrl(
            'marketplace/product_downloadable_file/upload',
            ['type' => 'samples', '_secure' => true]
        );
        $this->getSamplesConfig()->setUrl($url);
        $this->getSamplesConfig()->setParams(['form_key' => $this->getFormKey()]);
        $this->getSamplesConfig()->setFileField('samples');
        $this->getSamplesConfig()->setFilters(['all' => ['label' => __('All Files'), 'files' => ['*.*']]]);
        $this->getSamplesConfig()->setReplaceBrowseWithRemove(true);
        $this->getSamplesConfig()->setWidth('32');
        $this->getSamplesConfig()->setHideUploadButton(true);
        return $this->_jsonEncoder->encode($this->getSamplesConfig()->getData());
    }

    /**
     * Retrieve config object
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSamplesConfig()
    {
        if ($this->_config === null) {
            $this->_config = new \Magento\Framework\DataObject();
        }

        return $this->_config;
    }
}
