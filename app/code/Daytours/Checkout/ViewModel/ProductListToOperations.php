<?php


namespace Daytours\Checkout\ViewModel;


use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Api\OrderRepositoryInterface;


class ProductListToOperations extends \Magento\Framework\View\Element\Template implements ArgumentInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        Template\Context $context,
        array $data = [],
        ?OrderRepositoryInterface $orderRepository = null
    )
    {
        $this->orderRepository = $orderRepository ?: ObjectManager::getInstance()->get(OrderRepositoryInterface::class);

        parent::__construct($context, $data);
    }

    public function getOrderById($orderId){
        if ($orderId) {
            return $this->orderRepository->get($orderId);
        }
        return null;
    }

    public function getAllOptions($itemOptions){
        $result = [];
        $options = $itemOptions;
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }
        return $result;

    }
}