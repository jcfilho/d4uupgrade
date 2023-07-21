<?php

namespace Daytours\EditOrder\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Controller\AbstractController\OrderViewAuthorization;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Registry;
use Daytours\EditOrder\Model\Order\Order as OrderModel;
use Magento\Framework\Data\Form\FormKey\Validator;

abstract class Order extends Action
{
    protected $_pageFactory;

    protected $orderAuthorization;

    protected $orderFactory;

    protected $registry;

    protected $order;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        OrderViewAuthorization $orderAuthorization,
        OrderRepositoryInterface $orderFactory,
        Registry $registry,
        OrderModel $order,
        Validator $formKeyValidator
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->orderAuthorization = $orderAuthorization;
        $this->orderFactory = $orderFactory;
        $this->registry = $registry;
        $this->order = $order;
        $this->formKeyValidator = $formKeyValidator;

        return parent::__construct($context);
    }
}