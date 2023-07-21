<?php 
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Product;

class Set extends \Magebay\Marketplace\Controller\Product\Account{
	
	protected $resultPageFactory;	
	protected $_customerSession;	
	
	public function __construct(	
		\Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
	){
		$this->resultPageFactory=$resultPageFactory;
		parent::__construct($context, $customerSession);
	}
	
    public function execute()
    {
        $isseller=$this->_objectManager->get('Magebay\Marketplace\Helper\Data')->checkIsSeller();
        if($isseller){
			$resultPageFactory = $this->resultPageFactory->create();
			$resultPageFactory->getConfig()->getTitle()->set(__('Marketplace Add New Product'));
			if($breadcrumbs = $resultPageFactory->getLayout()->getBlock('breadcrumbs')){
				$breadcrumbs->addCrumb('home',
					[
						'label' => __('Market Place'),
						'title' => __('Market Place'),
						'link' => $this->_url->getUrl('')
					]
				);
				$breadcrumbs->addCrumb('market_menu_withdraw_detail',
					[
						'label' => __('New Product'),
						'title' => __('New Product')
					]
				); 
			}			
			return $resultPageFactory;
        }else{
            $resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setPath('marketplace/seller/become');
			return $resultRedirect;
        }		
	}
}