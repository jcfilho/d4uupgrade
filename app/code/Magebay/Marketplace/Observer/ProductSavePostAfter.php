<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use \Magento\Framework\App\RequestInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;

class ProductSavePostAfter implements ObserverInterface
{
    protected $_storeManager;
	protected $_scopeConfig;
	protected $_request;
	protected $_customerCollectionFactory;
	protected $_objectManager;
	protected $_sessioncustomer;	
    protected $_timezone;
    protected $messageManager;	
	
	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
		\Magento\Customer\Model\Session $sessioncustomer,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		RequestInterface $request,
		ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
		array $data = []		
	){
        $this->_request = $request;        
        $this->_storeManager = $storeManager;        
		$this->_scopeConfig = $scopeConfig;
        $this->_customerCollectionFactory = $customerCollectionFactory;        
		$this->sessioncustomer = $sessioncustomer;  		
		$this->_objectManager = $objectManager;  		
		$this->_messageManager = $messageManager;  	
        $this->_timezone = $timezone;	
	}
	
	public function execute(\Magento\Framework\Event\Observer $observer){
        if($observer->getActionMk() == 'add'){
    		try{
    			$customerData=$this->sessioncustomer->getCustomer()->getData();
    			$_model=$this->_objectManager->create('Magebay\Marketplace\Model\Products');
    			$product_id=$observer->getProductId();
    			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                if($objectManager->create('Magebay\Marketplace\Helper\Data')->getProductApprovalRequired()){
                    $product_status = 0;
                    $product = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
                    $product->setStatus(2);
                    $product->save();
                }else{
                    $product_status = 1;
                }					
    			$_params['product_id']=$product_id;
    			if($observer->getApproval() == 3){
                    $_params['status']=3;
                }else{
                    $_params['status']=$product_status;
                }		
    			if($customerData['entity_id']){
    				$customerId=$customerData['entity_id'];
    				$_params['user_id']=$customerId;
    			}
    			$storeid=$this->_storeManager->getStore(true)->getId();
    			$_params['store_ids']=$storeid;			
    			$_params['adminassign']=0;	
                $_params['created']=date('Y-m-d H:i:s',$this->_timezone->scopeTimeStamp());
                $_params['modified']=date('Y-m-d H:i:s',$this->_timezone->scopeTimeStamp());
    								
    			if($observer->getProductType()==ConfigurableProduct::TYPE_CODE){
    				$productids=$this->getProductSimpleOfProductConfig($product_id);	
    				$_model1=$this->_objectManager->create('Magebay\Marketplace\Model\ResourceModel\Products');
    				$_arrayparam=array();
    				$_arrayparam[]=$_params;
    				foreach($productids as $key=>$val){
    					$_params['product_id']=$val;
    					$_arrayparam[]=$_params;
    				}
    				$_model1->getConnection()->insertMultiple($_model1->getMainTable(), $_arrayparam);
    			}else{
    				$_model->addData($_params);
    				$_model->save();				
    			}	
                
                $moduleManager = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Framework\Module\Manager');
                //Membership 
                if($moduleManager->isEnabled('Magebay_SellerMembership') && \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\Marketplace\Helper\Data')->getSellerMembershipIsEnabled()){
                    $membershipData = \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\SellerMembership\Model\SellerMembership')
                                                                                                 ->getCollection()
                                                                                                 ->addFieldToFilter('seller_id',\Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Customer\Model\Session')->getId())
                                                                                                 ->getFirstItem();
                    if($membershipData['id']){
                        $seller_membership_old = \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\SellerMembership\Model\SellerMembership')->load($membershipData['id']);
                        $seller_membership_old->setRemainingNumberProduct($membershipData['remaining_number_product']-1);
                        $seller_membership_old->save();
                    }
                }
                //End membership
    		
    		}catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_messageManager->addError(nl2br($e->getMessage()));            
            } catch (\Exception $e) {
                $this->_messageManager->addException($e, __('Something went wrong while saving this .').' '.$e->getMessage());            
            }
        }else{
            $customerData=$this->sessioncustomer->getCustomer()->getData();
            $product_id=$observer->getProductId();
            $_model=$this->_objectManager->create('Magebay\Marketplace\Model\Products')->getCollection()
                                                                                        ->addFieldToFilter('product_id',$product_id)
                                                                                        ->addFieldToFilter('user_id',$customerData['entity_id'])
                                                                                        ->getFirstItem();
            $_model->setModified(date('Y-m-d H:i:s',$this->_timezone->scopeTimeStamp()));
            if($_model->getStatus() == 1){
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                if($objectManager->create('Magebay\Marketplace\Helper\Data')->getProductUpdateApprovalRequired()){
                    $product_status = 0;
                    $product = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
                    $product->setStatus(2);
                    $product->save();
                }else{
                    $product_status = 1;
                }  
                if($observer->getApproval() == 3){
                    $_params['status']=3;
                }else{
                    $_params['status']=$product_status;
                }
                $_model->setStatus($_params['status']); 
                $_model->save(); 
            }
        }
	}

	public function getProductSimpleOfProductConfig($productid){
		$relation=$this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Relation');
		$select=$relation->getConnection()->select()->from(
            $relation->getMainTable(),
            ['child_id']
        )->where(
            'parent_id = ?',
            $productid
        );
		return $relation->getConnection()->fetchCol($select);
	}			
} 
