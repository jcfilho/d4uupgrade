<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Seller;

class Become extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
	protected $_customerSession;
    protected $_customerCollectionFactory;
    //protected $_objectManager;
    //protected $_messageManager;  
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
        //\Magento\Framework\ObjectManagerInterface $objectManager,
        //\Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        $this->_customerCollectionFactory = $customerCollectionFactory;   
        //$this->_objectManager = $objectManager;  
        //$this->_messageManager = $messageManager;         
        parent::__construct($context);
    }
    
    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        //Register seller if customer request information
        if($this->getRequest()->isPost()){
            try{	
                $_data = $this->getRequest()->getParams(); 
                $customerSession = $this->_customerSession;
                $sellerid = $customerSession->getId();
                $_customerconllection = $this->_customerCollectionFactory->create()->addFieldToFilter('entity_id',$sellerid);                
    			$_customer = $_customerconllection->getData();
    			$_newdata['email'] = $_customer[0]['email'];
    			$_newdata['name'] = $_customer[0]['firstname'].$_customer[0]['lastname'];
    			$_newdata['is_vendor'] = 1 ;
                $_newdata['storetitle'] = $_data['storetitle'];
                $_newdata['contactnumber'] = $_data['contactnumber'];                                
    			$_newdata['storeurl'] = $_data['shopurl'];
    			$_newdata['user_id'] = $_customer[0]['entity_id'];
    			$_newdata['stores_id'] = $_customer[0]['store_id'];
    			$_newdata['created'] = $_customer[0]['created_at'];
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                if($objectManager->create('Magebay\Marketplace\Helper\Data')->getSellerApprovalRequired()){
                    $status = 0;
                }else{
                    $status = 1;
                }
                $_newdata['userstatus']=$status; 
    			$_model = $this->_objectManager->create('Magebay\Marketplace\Model\Sellers');
    			$_model->addData($_newdata);
    			$_model->save();
                $this->messageManager->addSuccess('You have been registed seller, please wait for approval from admin');    
                $this->_redirect('marketplace/seller/become'); 
    		}catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError(nl2br($e->getMessage()));            
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving this .').' '.$e->getMessage());            
            }	
        }
        
		$isseller = $this->_objectManager->get('Magebay\Marketplace\Helper\Data')->checkIsSeller();
        if(!$isseller){
    		$customerSession = $this->_customerSession;
    		if(!$customerSession->isLoggedIn())
    		{
    			$this->_redirect('marketplace');
    		}
            $resultPageFactory = $this->resultPageFactory->create();
    		$resultPageFactory->getConfig()->getTitle()->set(__('Marketplace Become a Seller'));	
    		if($breadcrumbs = $resultPageFactory->getLayout()->getBlock('breadcrumbs')){
                $breadcrumbs->addCrumb('home',
                    [
                        'label' => __('Market Place'),
                        'title' => __('Market Place'),
                        'link' => $this->_url->getUrl('')
                    ]
                );
                $breadcrumbs->addCrumb('market_menu_become_seller',
                    [
                        'label' => __('Marketplace Become a Seller'),
                        'title' => __('Marketplace Become a Seller')
                    ]
                ); 
            }
            return $resultPageFactory;
        }else{
            $this->_redirect('marketplace/seller/dashboard');
        }
    } 
}