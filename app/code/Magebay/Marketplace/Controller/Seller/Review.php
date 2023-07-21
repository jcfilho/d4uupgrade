<?php
/**
 * @Author      : Dream
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Seller;

class Review extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
	/**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;
	 /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
	/**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;
	
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Framework\Registry $coreRegistry,
		\Magento\Customer\Model\CustomerFactory $customerFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
		$this->_resource = $resource;
		$this->_coreRegistry = $coreRegistry;
		$this->_customerFactory = $customerFactory;
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        $resultPageFactory = $this->resultPageFactory->create();
		$sellerName = $this->getRequest()->getParam('vendor','');
		$okSeller = false;
		$strName = '';
		if($sellerName != '')
		{
			$tableSellers = $this->_resource->getTableName('multivendor_user');
			$customerModel = $this->_customerFactory->create();
			$sellers = $customerModel->getCollection();
			$sellers->getSelect()->joinLeft(array('table_sellers'=>$tableSellers),'e.entity_id = table_sellers.user_id',array('*'))
				->where('table_sellers.userstatus = 1')
				->where('table_sellers.storeurl = ?',$sellerName);
			$seller = $sellers->getFirstItem();
			if($seller && $seller->getId())
			{
				$okSeller = true;
				$strName = $seller->getStoretitle();
				$this->_coreRegistry->register('seller_profile', $seller);
			}
		}
		if(!$okSeller)
		{
			$this->_redirect('marketplace');
		}
        $resultPageFactory->getConfig()->getTitle()->set($strName);
        if($breadcrumbs = $resultPageFactory->getLayout()->getBlock('breadcrumbs')){
            $breadcrumbs->addCrumb('home',
                [
                    'label' => __('Market Place'),
                    'title' => __('Market Place'),
                    'link' => $this->_url->getUrl('')
                ]
            );
            $breadcrumbs->addCrumb('market_menu',
                [
                    'label' => __('Review Seller'),
                    'title' => __('Review Seller')
                ]
            ); 
        }        
        return $resultPageFactory;
    } 
}