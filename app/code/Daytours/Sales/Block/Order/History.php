<?php

namespace Daytours\Sales\Block\Order;

class History extends \Magento\Sales\Block\Order\History
{
    /**
     * @var \Daytours\EditOrder\Helper\Data
     */
    protected $_editOrderHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Daytours\EditOrder\Helper\Data $editOrderHelper,
        array $data = []
    )
    {
        $this->_editOrderHelper = $editOrderHelper;
        parent::__construct($context, $orderCollectionFactory, $customerSession, $orderConfig, $data);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getCompleteUrl($order)
    {
        return $this->_editOrderHelper->getURLEncrypted($order->getId(),$order->getCustomerEmail(),$order->getStoreId());
    }

    /**
     * Determine if an order is incomplete
     *
     * @param object $order
     * @return string
     */
    public function isOrderIncomplete($order)
    {
        return $this->_editOrderHelper->ifMissingFieldsToPostOrder($order) && !$order->getPostVenta();
    }
}