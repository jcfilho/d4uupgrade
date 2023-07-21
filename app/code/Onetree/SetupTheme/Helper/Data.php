<?php

namespace Onetree\SetupTheme\Helper;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Data
 * @package Onetree\SetupTheme\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper

{
    /**
     * Entity type code
     */
    const ENTITY_TYPE = 'cms-page';

    /** @var \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewrite */
    private $urlRewrite;

    /** @var  \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory $urlRewriteResource */
    private $urlRewriteCollection;

    /** @var  \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteFactory $urlRewriteResource */
    private $urlRewriteResource;

     /**
      * @param \Magento\Framework\App\Helper\Context $context
      * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewrite
      * @param \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteFactory $urlRewriteResource
      * @param \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory $urlRewriteCollection
     */
    public function __construct(\Magento\Framework\App\Helper\Context $context,
                                \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewrite,
                                \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteFactory $urlRewriteResource,
                                \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory $urlRewriteCollection)
    {
//        $this->_moduleManager = $context->getModuleManager();
//        $this->_logger = $context->getLogger();
//        $this->_request = $context->getRequest();
//        $this->_urlBuilder = $context->getUrlBuilder();
//        $this->_httpHeader = $context->getHttpHeader();
//        $this->_eventManager = $context->getEventManager();
//        $this->_remoteAddress = $context->getRemoteAddress();
//        $this->_cacheConfig = $context->getCacheConfig();
//        $this->urlEncoder = $context->getUrlEncoder();
//        $this->urlDecoder = $context->getUrlDecoder();
//        $this->scopeConfig = $context->getScopeConfig();
        $this->urlRewrite           = $urlRewrite;
        $this->urlRewriteResource   = $urlRewriteResource;
        $this->urlRewriteCollection = $urlRewriteCollection;
        parent::__construct($context);

    }

    /**
     * Create url rewrite object
     *
     * @param int $storeId
     * @param int $redirectType
     */
    protected function removeCMSUrlRewrite($storeId, $identifier)
    {
        /** @var \Magento\UrlRewrite\Model\UrlRewrite $urlRewrite */
        $urlRewrite = $this->urlRewrite->create();

        /** @var  \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection $collection */
        $collection = $this->urlRewriteCollection->create();
        $collection->addFieldToFilter("entity_type", self::ENTITY_TYPE)
            ->addFieldToFilter("request_path", $identifier);
        if($storeId && (count($storeId) > 1 || (count($storeId) == 1 && $storeId[0] != 0))){
            $collection->addFieldToFilter("store_id", array("in"=> $storeId));
        }

        if(0 < $collection->getSize()) {
            foreach($collection->getItems() as $item) {
                /** @var \Magento\UrlRewrite\Model\UrlRewrite $urlRewrite */
                $urlRewrite     = $this->urlRewrite->create();
                $urlRewriteItem = $this->urlRewriteResource->load($urlRewrite,$item->getId());
                //$urlRewriteItem->load($item->getId());
                $urlRewriteItem->delete();
            }
        }
    }
}
