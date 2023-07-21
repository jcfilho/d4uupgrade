<?php

namespace Magebay\Marketplace\Controller\Product\Grouped;

use Magento\Framework\Registry;
use Magento\Catalog\Model\ProductFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\ResultFactory;

class Popup extends \Magebay\Marketplace\Controller\Product\Account
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $factory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ProductFactory $factory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
        Registry $registry,
        ProductFactory $factory,
        LoggerInterface $logger
    ) {
        $this->registry = $registry;
        $this->factory = $factory;
        $this->logger = $logger;
        parent::__construct($context, $customerSession);
    }

    /**
     * Get associated grouped products grid popup
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('id');

        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->factory->create();
        $product->setStoreId($this->getRequest()->getParam('store', 0));

        $typeId = $this->getRequest()->getParam('type');
        if (!$productId && $typeId) {
            $product->setTypeId($typeId);
        }
        $product->setData('_edit_mode', true);

        if ($productId) {
            try {
                $product->load($productId);
            } catch (\Exception $e) {
                $product->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE);
                $this->logger->critical($e);
            }
        }

        $setId = (int)$this->getRequest()->getParam('set');
        if ($setId) {
            $product->setAttributeSetId($setId);
        }
        $this->registry->register('current_product', $product);
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        return $resultLayout;
    }
}
