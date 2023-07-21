<?php
/**
 * @Author      : Hau+Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Seller;
class Dashboard extends \Magento\Framework\App\Action\Action{
    
	/**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,		
        \Magento\Framework\View\Result\PageFactory $resultPageFactory		
	){
		parent::__construct($context);
		$this->_resultPageFactory=$resultPageFactory;		
	}
	
	public function execute(){
        $isseller=$this->_objectManager->get('Magebay\Marketplace\Helper\Data')->checkIsSeller();
        if($isseller){
    		$customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
    		if(!$customerSession->isLoggedIn())
    		{
    			$resultRedirect = $this->resultRedirectFactory->create();
    			$resultRedirect->setPath('marketplace');
    			return $resultRedirect;
    		}
            		
            $resultPageFactory = $this->_resultPageFactory->create();
    		$resultPageFactory->getConfig()->getTitle()->set(__('Seller Dashboard'));	
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
                            'label' => __('Seller Dashboard'),
                            'title' => __('Seller Dashboard')
                        ]
                    );
                }		
            return $resultPageFactory;		
    	}else{
            $this->_redirect('marketplace/seller/become');
        }
    }
}