<?php

namespace Daytours\PartialPayment\Block;

use Daytours\PartialPayment\Model\PartialInvoice;
use Daytours\PartialPayment\Model\PartialInvoiceFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class PartialPayment extends \Magento\Framework\View\Element\Template
{
    protected $_orderCollectionFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context);
    }


    public function getOrdersWithDueAmount()
    {
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('base_total_due', ['neq' => '0']);
        return $collection;
    }


    /**
     * @return \Magento\Sales\Api\Data\OrderInterface[] Array of collection items.
     */
    public function getOrdersWithDueAmountByCustomerLogged()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        if ($customerSession->isLoggedIn()) {
            $customerId = $customerSession->getCustomerId();
            $collection = $this->_orderCollectionFactory->create($customerId)
                ->addFieldToSelect('*')
                ->addFieldToFilter('base_total_due', ['neq' => '0'])
                ->setOrder(
                    'created_at',
                    'desc'
                );
            return $collection->getItems();
        }
        return null;
    }
}
