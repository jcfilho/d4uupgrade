<?php

namespace Daytours\EditOrder\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Daytours\EditOrder\Model\Order\Email\LogFactory;
use Daytours\EditOrder\Model\Order\Email\Sender;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magebay\Bookingsystem\Helper\BkHelperDate;

class OrderSender extends AbstractHelper
{
    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * @var Sender
     */
    protected $sender;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var Option
     */
    protected $optionHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     **/
    protected $_timezone;

    /**
     * @var \Magebay\Bookingsystem\Helper\BkHelperDate
     **/
    protected $_bkHelperDate;
    /**
     * @var \Daytours\EditOrder\Helper\Data
     */
    private $editOrderHelper;

    /**
     * Order sender constructor
     *
     * @param Context $context
     * @param LogFactory $logFactory
     * @param Sender $sender
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Option $optionHelper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magebay\Bookingsystem\Helper\BkHelperDate $bkHelperDate
     */
    public function __construct(
        Context $context,
        LogFactory $logFactory,
        Sender $sender,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Option $optionHelper,
        TimezoneInterface $timezone,
        BkHelperDate $bkHelperDate,
        \Daytours\EditOrder\Helper\Data $editOrderHelper
    )
    {
        $this->logFactory = $logFactory;
        $this->sender = $sender;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->optionHelper = $optionHelper;
        $this->_timezone = $timezone;
        $this->_bkHelperDate = $bkHelperDate;

        parent::__construct($context);
        $this->editOrderHelper = $editOrderHelper;
    }

    /**
     * Look for orders with missing data and send a reminder email
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendMissingOrderDataEmails()
    {
        return;
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(
                'status',
                array('canceled', 'closed', 'complete'),
                'nin'
            )->create();
        $orders = $this->orderRepository->getList($searchCriteria);
        foreach ($orders->getItems() as $order) {
//            if( $order->getId() == 310 ){
                $this->sendMissingDataOrderEmail($order);
//            }
        };
    }

    /**
     * Check missing order data and send a reminder email
     *
     * @param \Magento\Catalog\Model\Order $order
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendMissingDataOrderEmail($order)
    {
        return;
        if ($this->hasMissingData($order)) {
            $countdown = $this->getCountdown($order);
            if ($countdown >= 0 && $this->checkFrequency($order, $countdown)) {
                $this->sender->send($order, $countdown);
                $this->logEmailSend($order);
            }
        }
    }

    /**
     * Check missing order data and send a reminder email
     *
     * @param \Magento\Catalog\Model\Order $order
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendMissingDataOrderEmailAfterOrder($order)
    {
        if ($this->hasMissingData($order)) {
            $this->sender->send($order, -1);
            $this->logEmailSend($order);
        }
    }

    /**
     * Check if there is some missing data
     *
     * @param \Magento\Catalog\Model\Order $order
     * @return bool
     */
    public function hasMissingData($order)
    {

        return $this->editOrderHelper->ifMissingFieldsToPostOrder($order);

//        foreach ($order->getItems() as $orderItem) {
//            $product = $orderItem->getProduct();
//            $options = $orderItem->getProductOptions();
//            if ($product->getTypeInstance()->hasOptions($product)) {
//                foreach ($product->getOptions() as $customOption) {
//                    if ($this->optionHelper->isCustomOptionEditable($customOption)) {
//                        $option = $this->optionHelper->getOptionById($options, $customOption->getId());
//                        if (!$option) {
//                            return true;
//                        }
//                    }
//                }
//            }
//        }
//
//        return false;
    }

    /**
     * Get remaining days
     *
     * @param \Magento\Catalog\Model\Order $order
     * @return int
     */
    public function getCountdown($order)
    {
        $checkIns = array();
        foreach ($order->getItems() as $orderItem) {
            if( $orderItem->getProductOptions() ){
                if( isset($orderItem->getProductOptions()['info_buyRequest']) ){
                    if( isset($orderItem->getProductOptions()['info_buyRequest']['temp_check_in']) ){
                        $checkin = $orderItem->getProductOptions()['info_buyRequest']['temp_check_in'];
                        $checkIns[] = date('Y-m-d', strtotime($checkin));
                    }
                }
            }
        }
        usort($checkIns, function ($a, $b) {
            return strtotime($a) - strtotime($b);
        });
        if (count($checkIns)) {
            $storeDate = $this->_timezone->date();
            $start = new \DateTime($storeDate->format('Y-m-d'));
            $end = new \DateTime($checkIns[0]);
            $diff = $start->diff($end);
            return $diff->days;
        }

        return -1;
    }

    /**
     * Check frequency for sending email
     *
     * @param \Magento\Catalog\Model\Order $order
     * @param int $countdown
     * @return bool
     */
    public function checkFrequency($order, $countdown)
    {
        $collection = $this->logFactory->create()->getCollection();
        $collection->addFieldToFilter('order_id', ["eq" => $order->getId()]);

        if ($collection->count() == 0) {
            return true;
        }

        foreach ($collection as $log) {
            $start = new \DateTime($log->getSentAt());
            $storeDate = $this->_timezone->date();
            $end = new \DateTime($storeDate->format('Y-m-d H:i:s'));
            $diff = $start->diff($end);

            if ($countdown < 7) {
                $hours = ($diff->days * 24) + $diff->h;
                if ($hours >= 12) {
                    return true;
                }
            } else if ($countdown < 30) {
                if ($diff->days >= 1) {
                    return true;
                }
            } else {
                if ($diff->days >= 7) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Logs last time the email was sent
     *
     * @param \Magento\Catalog\Model\Order $order
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function logEmailSend($order)
    {
        $collection = $this->logFactory->create()->getCollection();
        $collection->addFieldToFilter('order_id', ["eq" => $order->getId()]);
        $storeDate = $this->_timezone->date();
        $sentAt = $storeDate->format('Y-m-d H:i:s');

        if ($collection->count()) {
            foreach ($collection as $log) {
                $log->setCount($log->getCount() + 1)
                    ->setSentAt($sentAt)
                    ->save();
            }
        } else {
            $this->logFactory
                ->create()
                ->setOrderId($order->getId())
                ->setCount(1)
                ->setSentAt($sentAt)
                ->save();
        }
    }
}