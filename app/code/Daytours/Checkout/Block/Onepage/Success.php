<?php


namespace Daytours\Checkout\Block\Onepage;


class Success extends \DigitalHub\Ebanx\Block\Success
{

    const PATH_NAME_STORE = 'general/store_information/name';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Daytours\EditOrder\Helper\Data
     */
    private $dataEditOrder;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * Success constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Daytours\EditOrder\Helper\Data $dataEditOrder
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Daytours\EditOrder\Helper\Data $dataEditOrder,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = [])
    {
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
        $this->scopeConfig = $scopeConfig;
        $this->dataEditOrder = $dataEditOrder;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Get Store name
     *
     * @return string
     */
    public function getStoreName()
    {
        return $this->scopeConfig->getValue(self::PATH_NAME_STORE);
    }


    public function ifOrderIsNormal($orderId){
        return $this->dataEditOrder->isVisibleCompleteOrderButton($orderId);
    }

    /**
     * Prepares block data
     *
     * @return void
     */
    protected function prepareBlockData()
    {
        $order = $this->_checkoutSession->getLastRealOrder();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $dateTimeFormatter = $objectManager->get('Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface');

        $orderStaticDate = new \DateTime($order->getCreatedAt());
        $orderDataConverted = $dateTimeFormatter->formatObject($orderStaticDate, \IntlDateFormatter::SHORT);

        $this->addData(
            [
                'is_order_visible' => $this->isVisible($order),
                'view_order_url' => $this->getUrl(
                    'sales/order/view/',
                    ['order_id' => $order->getEntityId()]
                ),
                'print_url' => $this->getUrl(
                    'sales/order/print',
                    ['order_id' => $order->getEntityId()]
                ),
                'can_print_order' => $this->isVisible($order),
                'can_view_order'  => $this->canViewOrder($order),
                'order_id'  => $order->getIncrementId(),
                'order_date' => $orderDataConverted,
                'order_total' => $order->getGrandTotal(),
                'order_payment_method' => $order->getPayment()->getMethodInstance()->getCode(),
                'order_payment_method_title' => $order->getPayment()->getMethodInstance()->getTitle(),
                'order_customer_firstname' => $order->getCustomerFirstname(),
                'order_customer_email' => $order->getCustomerEmail(),
                'transaction_data' => $order->getPayment()->getAdditionalInformation('transaction_data'),
                'date_time_formatter' => $dateTimeFormatter,
                'customer_id' => $order->getCustomerId()
            ]
        );
    }

}