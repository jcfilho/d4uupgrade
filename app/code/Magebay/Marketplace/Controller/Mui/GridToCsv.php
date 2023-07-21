<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebay\Marketplace\Controller\Mui;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Controller\UiActionInterface;

use Magento\Ui\Model\Export\ConvertToCsv;
use Magento\Ui\Model\Export\MetadataProvider;
use Magento\Framework\App\Response\Http\FileFactory;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Ui\Component\MassAction\Filter;



/**
 * Class Render
 */
class GridToCsv extends \Magento\Framework\App\Action\Action
{
    /**
     * @var ConvertToCsv
     */
    protected $converter;

    /**
     * @var FileFactory
     */
    protected $fileFactory;
	
	protected $_resource;
	protected $_modelSession;

    /**
     * @param Context $context
     * @param ConvertToCsv $converter
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        ConvertToCsv $converter,
		//Magento\Sales\Model\ResourceModel\Order\CollectionFactory $converter,
        FileFactory $fileFactory,
		Filter $filter,
		Filesystem $filesystem,
		MetadataProvider $metadataProvider,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Customer\Model\Session $modelSession
    ) {
        parent::__construct($context);
        $this->converter = $converter;
        $this->fileFactory = $fileFactory;
		$this->filter = $filter;
		
		$this->metadataProvider = $metadataProvider;
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->formatPrice = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
		$this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
		$this->pageSize = 200;
		
		$this->_resource = $resource;
		$this->_modelSession = $modelSession;
		
    }

    /**
     * Export data provider to CSV
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        //return $this->fileFactory->create('export.csv', $this->converter->getCsvFile(), 'var');
       return $this->fileFactory->create('export.csv', $this->getCsvFile(), 'var');
    }
	
	public function getCsvFile()
    {
        $component = $this->filter->getComponent();

        $name = md5(microtime());
        $file = 'export/'. $component->getName() . $name . '.csv';

		//echo $component->getName(); exit();
		
        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $dataProvider = $component->getContext()->getDataProvider();
        $fields = $this->metadataProvider->getFields($component);
        $options = $this->metadataProvider->getOptions();

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->metadataProvider->getHeaders($component));
        $i = 1;

        $totalCount = (int) $dataProvider->getSearchResult()->getTotalCount();
		
		$seller = $this->_modelSession;
		$tableSalelist = $this->_resource->getTableName('multivendor_saleslist');
		$collection = $dataProvider->getSearchResult();
		$collection->getSelect()->joinLeft(
			array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
			array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
		);
		$collection->getSelect()->where('mk_sales_list.sellerid=?', $seller->getId() );
		$collection->getSelect()->group('main_table.entity_id');
		
		$totalCount2 = (int) $collection->count();
		
		while ($totalCount > 0) {
           // $items = $dataProvider->getSearchResult()->getItems();
			$items = null;
			
			/* $dataProvider->getSearchResult()->setCurPage( 1 )->setPageSize( $this->pageSize );
			$items = $dataProvider->getSearchResult()->setCurPage( 1 )->setPageSize( $this->pageSize )->getItems(); */
			
			$items = $collection->setCurPage( 1 )->setPageSize( $this->pageSize )->getItems(); 
			
            foreach ($items as $item) {
                //$this->metadataProvider->convertDate($item, $component->getName());
                //$stream->writeCsv($this->metadataProvider->getRowData($item, $fields, $options));
				$row = [];
				foreach ( $fields as $option) {
					switch ($option) {
						case 'increment_id':
							$row[] = $item->getData($option);
							break;
						case 'store_id':
							$row[] = $options['store_id'][ $item->getData($option) ];
							break;
						case 'base_grand_total':	
							$row[] = $this->formatPrice->currency( $item->getData($option) ,true,false);
							break;
						case 'grand_total':	
							$row[] = $this->formatPrice->currency( $item->getData($option) ,true,false);
							break;	
							
						default:
							$row[] = $item->getData($option);
					}
				}	
				$stream->writeCsv(  $row );
            }
			++$i;
            $totalCount = $totalCount - $this->pageSize;
		}
        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    } 
}
