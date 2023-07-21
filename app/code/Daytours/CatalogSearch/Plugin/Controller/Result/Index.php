<?php
/**
 * Created by PhpStorm.
 * User: filho
 * Date: 6/5/18
 * Time: 10:13 AM
 */
namespace Daytours\CatalogSearch\Plugin\Controller\Result;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\UrlInterface;

class Index{
    /**
     * Catalog session
     *
     * @var Session
     */
    protected $_catalogSession;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var QueryFactory
     */
    private $_queryFactory;

    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $_view;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $_redirect;
    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * Index constructor.
     * @param Context $context
     * @param Session $catalogSession
     * @param StoreManagerInterface $storeManager
     * @param QueryFactory $queryFactory
     * @param Resolver $layerResolver
     * @param UrlInterface $url
     */
    public function __construct(
        Context $context,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver,
        UrlInterface $url
    ) {

        $this->_storeManager = $storeManager;
        $this->_catalogSession = $catalogSession;
        $this->_queryFactory = $queryFactory;
        $this->layerResolver = $layerResolver;
        $this->_objectManager = $context->getObjectManager();
        $this->_view = $context->getView();
        $this->_redirect = $context->getRedirect();
        $this->url = $url;
    }

    /**
     * @param \Magento\CatalogSearch\Controller\Result\Index $subject
     * @param \Closure $proceed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundExecute(\Magento\CatalogSearch\Controller\Result\Index $subject, \Closure $proceed){
        $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);
        /* @var $query \Magento\Search\Model\Query */
        $query = $this->_queryFactory->get();

        $query->setStoreId($this->_storeManager->getStore()->getId());

        if ($query->getQueryText() != '') {
            if ($this->_objectManager->get(\Magento\CatalogSearch\Helper\Data::class)->isMinQueryLength()) {
                $query->setId(0)->setIsActive(1)->setIsProcessed(1);
            } else {
                $query->saveIncrementalPopularity();

                $redirect = $query->getRedirect();
                if ($redirect && $this->url->getCurrentUrl() !== $redirect) {
                    $subject->getResponse()->setRedirect($redirect);
                    return;
                }
            }

            $this->_objectManager->get(\Magento\CatalogSearch\Helper\Data::class)->checkNotes();

            $this->_view->loadLayout();

            $this->_view->getLayout()->unsetElement('catalogsearch.product.addto.compare');
            $this->_view->getLayout()->unsetElement('catalogsearch.product.addto');

            /** @var \Magento\Framework\View\Element\Magento\Framework\View\Element\BlockInterface|bool $blockSearchResultList */
            $blockSearchResultList = $this->_view->getLayout()->getBlock('search_result_list');
            if (!empty($blockSearchResultList)) {
                if ($blockSearchResultList->getLoadedProductCollection()->getSize() === 0) {
                    $this->_view->getPage()->getConfig()->addBodyClass('no-result-in-search');
                    $this->_view->getLayout()->unsetElement('div.sidebar.main');
                }
            }

            $this->_view->renderLayout();
        } else {
            $subject->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
        }
    }
}