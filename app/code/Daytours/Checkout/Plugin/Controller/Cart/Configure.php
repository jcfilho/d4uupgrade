<?php

namespace Daytours\Checkout\Plugin\Controller\Cart;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Configure
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Cart $cart
    )
    {
        $this->cart = $cart;
        $this->messageManager = $context->getMessageManager();
        $this->resultFactory = $context->getResultFactory();
        $this->_objectManager = $context->getObjectManager();
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
    }

    public function aroundExecute(\Magento\Checkout\Controller\Cart\Configure $subject, \Closure $proceed)
    {
        // Extract item and product to configure
        $id = (int)$subject->getRequest()->getParam('id');
        $productId = (int)$subject->getRequest()->getParam('product_id');
        $quoteItem = null;
        if ($id) {
            $quoteItem = $this->cart->getQuote()->getItemById($id);
            $quoteItem->setQty($quoteItem->getQtyCustom());
        }

        try {
            if (!$quoteItem || $productId != $quoteItem->getProduct()->getId()) {
                $this->messageManager->addError(__("We can't find the quote item."));
                return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('checkout/cart');
            }

            $params = new \Magento\Framework\DataObject();
            $params->setCategoryId(false);
            $params->setConfigureMode(true);
            $params->setBuyRequest($quoteItem->getBuyRequest());

            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $this->_objectManager->get(\Magento\Catalog\Helper\Product\View::class)
                ->prepareAndRender(
                    $resultPage,
                    $quoteItem->getProduct()->getId(),
                    $subject,
                    $params
                );
            return $resultPage;
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We cannot configure the product.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('checkout/cart');
        }
    }

}