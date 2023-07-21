<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebay\Marketplace\Controller\Seller\Order;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\Order\Pdf\Shipment;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Pdfshipments extends \Magebay\Marketplace\Controller\Seller\Order\AbstractMassAction
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var Shipment
     */
    protected $pdfShipment;

    /**
     * @var ShipmentCollectionFactory
     */
    protected $shipmentCollectionFactotory;
	
	protected $_resource;
	protected $_modelSession;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param DateTime $dateTime
     * @param FileFactory $fileFactory
     * @param Shipment $shipment
     * @param ShipmentCollectionFactory $shipmentCollectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        DateTime $dateTime,
        FileFactory $fileFactory,
        Shipment $shipment,
        ShipmentCollectionFactory $shipmentCollectionFactory,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Customer\Model\Session $modelSession
    ) {
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->pdfShipment = $shipment;
        $this->collectionFactory = $collectionFactory;
        $this->shipmentCollectionFactotory = $shipmentCollectionFactory;
		$this->_resource = $resource;
		$this->_modelSession = $modelSession;
        parent::__construct($context, $filter);
    }

    /**
     * Print shipments for selected orders
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface|\Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
		$seller = $this->_modelSession;
		$tableSalelist = $this->_resource->getTableName('multivendor_saleslist');
		$collection->getSelect()->joinLeft(
			array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
			array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
		);
		$collection->getSelect()->where('mk_sales_list.sellerid=?', $seller->getId() );
		$collection->getSelect()->group('main_table.entity_id');
		
        $shipmentsCollection = $this->shipmentCollectionFactotory
            ->create()
            ->setOrderFilter(['in' => $collection->getAllIds()]);
        if (!$shipmentsCollection->getSize()) {
            $this->messageManager->addError(__('There are no printable documents related to selected orders.'));
			$resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath( 'marketplace/seller/myOrders' );
        }
        return $this->fileFactory->create(
            sprintf('packingslip%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
            $this->pdfShipment->getPdf($shipmentsCollection->getItems())->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
