<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Daytours\Catalog\Rewrite\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Design;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class View extends \Magento\Catalog\Controller\Product\View
{

    public function __construct(
        Context $context,
        \Magento\Catalog\Helper\Product\View $viewHelper,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        ?LoggerInterface $logger = null,
        ?Data $jsonHelper = null,
        ?Design $catalogDesign = null,
        ?ProductRepositoryInterface $productRepository = null,
        ?StoreManagerInterface $storeManager = null
    )
    {
        parent::__construct(
            $context,
            $viewHelper,
            $resultForwardFactory,
            $resultPageFactory,
            $logger,
            $jsonHelper,
            $catalogDesign,
            $productRepository,
            $storeManager
        );

        $this->viewHelper = $viewHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger ?: ObjectManager::getInstance()
            ->get(LoggerInterface::class);
        $this->jsonHelper = $jsonHelper ?: ObjectManager::getInstance()
            ->get(Data::class);
        $this->catalogDesign = $catalogDesign ?: ObjectManager::getInstance()
            ->get(Design::class);
        $this->productRepository = $productRepository ?: ObjectManager::getInstance()
            ->get(ProductRepositoryInterface::class);
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);
    }

    /**
     * Product view action
     *
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        // Get initial data from request
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId = (int) $this->getRequest()->getParam('id');
        $specifyOptions = $this->getRequest()->getParam('options');

        if (
            !$this->_request->getParam('___from_store')
            && $this->_request->isPost()
            && $this->_request->getParam(self::PARAM_NAME_URL_ENCODED)
        ) {
            $product = $this->_initProduct();
            if (!$product) {
                return $this->noProductRedirect();
            }
            if ($specifyOptions) {
                $notice = $product->getTypeInstance()->getSpecifyOptionMessage();
                $this->messageManager->addNoticeMessage($notice);
            }
            if ($this->getRequest()->isAjax()) {
                $this->getResponse()->representJson(
                    $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode([
                        'backUrl' => $this->_redirect->getRedirectUrl()
                    ])
                );
                return;
            }
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setUrl($this->_url->getCurrentUrl());
            return $resultRedirect;
        }

        // Prepare helper and params
        $params = new \Magento\Framework\DataObject();
        $params->setCategoryId($categoryId);
        $params->setSpecifyOptions($specifyOptions);

        // Render page
        try {
            $this->applyCustomDesign($productId);
            $page = $this->resultPageFactory->create();
            $page->setPageLayout('2columns-right');
            $this->viewHelper->prepareAndRender($page, $productId, $this, $params);
            return $page;

//            $page = $this->resultPageFactory->create(false, ['isIsolated' => true]);
//            $pageConfig = $page->getConfig();
//            $pageConfig->setPageLayout('2columns-right');
//            $this->viewHelper->prepareAndRender($page, $productId, $this, $params);
//            return $page;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $this->noProductRedirect();
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }
    }

    /**
     * Apply custom design from product design settings
     *
     * @param int $productId
     * @throws NoSuchEntityException
     */
    private function applyCustomDesign(int $productId): void
    {
        $product = $this->productRepository->getById($productId, false, $this->storeManager->getStore()->getId());
        $settings = $this->catalogDesign->getDesignSettings($product);
        if ($settings->getCustomDesign()) {
            $this->catalogDesign->applyCustomDesign($settings->getCustomDesign());
        }
    }
}
