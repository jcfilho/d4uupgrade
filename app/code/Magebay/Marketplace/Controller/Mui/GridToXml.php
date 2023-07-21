<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebay\Marketplace\Controller\Mui;

use Magento\Framework\App\Action\Context;
use Magento\Ui\Model\Export\ConvertToXml;
use Magento\Framework\App\Response\Http\FileFactory;

use Magento\Ui\Model\Export\MetadataProvider;

use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Api\Search\SearchResultInterface;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Convert\Excel;
use Magento\Framework\Convert\ExcelFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Ui\Component\MassAction\Filter;

//use Magento\Framework\Data\SearchResultIteratorFactory;
use Magento\Ui\Model\Export\SearchResultIteratorFactory;

/**
 * Class Render
 */
class GridToXml extends \Magento\Framework\App\Action\Action
{
    /**
     * @var ConvertToXml
     */
    protected $converter;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @param Context $context
     * @param ConvertToXml $converter
     * @param FileFactory $fileFactory
     */
	 
	protected $directory;
    protected $metadataProvider;
    protected $excelFactory;
    protected $options;
    protected $iteratorFactory;
    protected $fields; 
	
	protected $_resource;
	protected $_modelSession;
	
	 
    public function __construct(
        Context $context,
        ConvertToXml $converter,
        FileFactory $fileFactory,
		Filesystem $filesystem,
        Filter $filter,
        MetadataProvider $metadataProvider,
        ExcelFactory $excelFactory,
        SearchResultIteratorFactory $iteratorFactory,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Customer\Model\Session $modelSession
    ) {
        parent::__construct($context);
        $this->converter = $converter;
        $this->fileFactory = $fileFactory;
		
		$this->filter = $filter;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->metadataProvider = $metadataProvider;
        $this->excelFactory = $excelFactory;
        $this->iteratorFactory = $iteratorFactory;
		$this->_resource = $resource;
		$this->_modelSession = $modelSession;
    }

    /**
     * Export data provider to XML
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        return $this->fileFactory->create('export.xml', $this->getXmlFile(), 'var');
    }
	
	public function getXmlFile()
    {
        $component = $this->filter->getComponent();

        $name = md5(microtime());
        $file = 'export/'. $component->getName() . $name . '.xml';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();

        $component->getContext()->getDataProvider()->setLimit(0, 0);

        /** @var SearchResultInterface $searchResult */
        $searchResult = $component->getContext()->getDataProvider()->getSearchResult();

        /** @var DocumentInterface[] $searchResultItems */
		/* Filter Seller Order */
		$seller = $this->_modelSession;
		$tableSalelist = $this->_resource->getTableName('multivendor_saleslist');
		$searchResult->getSelect()->joinLeft(
			array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
			array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
		);
		$searchResult->getSelect()->where('mk_sales_list.sellerid=?', $seller->getId() );
		$searchResult->getSelect()->group('main_table.entity_id');
		/* Filter Seller Order */
		
        $searchResultItems = $searchResult->getItems();

		$this->prepareItems($component->getName(), $searchResultItems);
		

        /** @var SearchResultIterator $searchResultIterator */
        $searchResultIterator = $this->iteratorFactory->create(['items' => $searchResultItems]);

		/*
		echo '<pre>';
		print_r( $searchResultIterator );
		exit(); 
		*/
		
        /** @var Excel $excel */
        $excel = $this->excelFactory->create([
            'iterator' => $searchResultIterator,
			'rowCallback'=> [$this, 'getRowData'],
        ]);

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();

        $excel->setDataHeader($this->metadataProvider->getHeaders($component));
		
        $excel->write($stream, $component->getName() . '.xml');
// exit();
        $stream->unlock();
        $stream->close();
		

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }
	
	protected function getOptions()
    {
        if (!$this->options) {
            $this->options = $this->metadataProvider->getOptions();
        }
        return $this->options;
    }

    /**
     * Returns DB fields list
     *
     * @return array
     */
    protected function getFields()
    {
        if (!$this->fields) {
            $component = $this->filter->getComponent();
            $this->fields = $this->metadataProvider->getFields($component);
        }
        return $this->fields;
    }
	
	public function getRowData(DocumentInterface $document)
    {
        return $this->metadataProvider->getRowData($document, $this->getFields(), $this->getOptions());
    }
	
	protected function prepareItems($componentName, array $items = [])
    {
        foreach ($items as $document) {
            $this->metadataProvider->convertDate($document, $componentName);
        }
    }
	
}
