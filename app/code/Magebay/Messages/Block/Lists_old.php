<?php
namespace Magebay\Messages\Block;

use Magento\Store\Model\ScopeInterface;

class Lists extends \Magento\Framework\View\Element\Template
{
    protected $_messagesCollectionFactory;

    protected $_messagesCollection;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magebay\Messages\Model\ResourceModel\Messages\CollectionFactory $messagesCollectionFactory,
        \Magento\Framework\View\Page\Config $postConfig,
		\Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
        $this->_filterProvider = $filterProvider;
        $this->_storeManager = $storeManager;
        $this->_messagesCollectionFactory = $messagesCollectionFactory;
        $this->pageConfig = $postConfig;
        $this->_customerSession = $customerSession;
    }
    
    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function _prepareMessagesCollection()
    {
        $this->_messagesCollection = $this->_messagesCollectionFactory->create()
            ->addFieldToFilter('user_id', $this->_customerSession->getId() )
            ->addActiveFilter();
            //->addStoreFilter($this->_storeManager->getStore()->getId())
            //->setOrder('publish_time', 'DESC');

        /*if ($this->getPageSize()) {
            $this->_messagesCollection->setPageSize($this->getPageSize());
        }*/
    }
    
    public function getMessagesCollection()
    {
        if (is_null($this->_messagesCollection)) {
            $this->_prepareMessagesCollection();
        }

        return $this->_messagesCollection;
    }	
	
	    
    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->_addBreadcrumbs();
        $this->pageConfig->getTitle()->set('Messages');        

        return parent::_prepareLayout();
    }

    /**
     * Prepare breadcrumbs
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function _addBreadcrumbs()
    {
        if ($this->_scopeConfig->getValue('web/default/show_cms_breadcrumbs', ScopeInterface::SCOPE_STORE)
            && ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs'))
        ) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'messages',
                [
                    'label' => __('Messages'),
                    'title' => __(sprintf('Go to Messages Home Page'))
                ]
            );
        }
    }
}