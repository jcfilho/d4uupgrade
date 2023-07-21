<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;
class Save extends \Magebay\Marketplace\Controller\Product\Account {

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $_timezone;
    
    /**
     * @var \Magento\Catalog\Model\Product\Copier
     */
    protected $productCopier;
	
    /**
     * @var Magebay\Marketplace\Controller\Product\Initialization\Helper
     */
    protected $initializationHelper;
	
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    
    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    protected $productTypeManager;        
	
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;
	
    /**
     * @var \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    protected $categoryLinkManagement;	
	
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magebay\Marketplace\Controller\Product\Builder $productBuilder
     * @param \Magebay\Marketplace\Controller\Product\Initialization\Helper $initializationHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
		\Magebay\Marketplace\Controller\Product\Builder $productBuilder,
		\Magebay\Marketplace\Controller\Product\Initialization\Helper $initializationHelper,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
		\Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager,
		\Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    ) {        
        $this->_storeManager = $storeManager; 
        $this->_timezone = $timezone;  
		$this->productBuilder = $productBuilder;
        $this->initializationHelper = $initializationHelper;
		$this->productRepository = $productRepository;
		$this->productTypeManager = $productTypeManager;
		$this->_formKeyValidator = $formKeyValidator;
		parent::__construct($context, $customerSession);
    }
	
    /**
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
	public function execute(){
		$resultRedirect = $this->resultRedirectFactory->create();	
        $customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
		if(!$customerSession->isLoggedIn())
		{
			$resultRedirect->setPath('marketplace');
			return $resultRedirect;
		}	
        $moduleManager = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Framework\Module\Manager');        
		$data = $this->getRequest()->getPostValue();
		$productId = 0;
		if($data){
			try {
				$product = $this->initializationHelper->productInitialize(
					$this->productBuilder->build($this->getRequest())
				);				
				$this->productTypeManager->processProduct($product);
				$originalSku = $product->getSku();
				$product->save();
				$this->handleImageProRemoveError($data, $product->getId());
                $this->getCategoryLinkManagement()->assignProductToCategories(
                    $product->getSku(),
                    $product->getCategoryIds()
                );
                $productId = $product->getId();
                $productAttributeSetId = $product->getAttributeSetId();
                $productTypeId = $product->getTypeId();
				$this->messageManager->addSuccess(__('Product submitted. Pending for admin approval. Please note that this process may take up to 24 hours'));
                if ($product->getSku() != $originalSku) {
                    $this->messageManager->addNotice(
                        __(
                            'SKU for product %1 has been changed to %2.',
                            $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($product->getName()),
                            $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($product->getSku())
                        )
                    );
                }
                
                $redirectBack = $this->getRequest()->getParam('back', false);
                if ($redirectBack === 'duplicate') {
                    $om = \Magento\Framework\App\ObjectManager::getInstance();
                    $productCopier = $om->create('\Magento\Catalog\Model\Product\Copier');
                    //Membership 
                    if($moduleManager->isEnabled('Magebay_SellerMembership') && \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\Marketplace\Helper\Data')->getSellerMembershipIsEnabled()){
                        $membershipData = \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\SellerMembership\Model\SellerMembership')
                                                                                                     ->getCollection()
                                                                                                     ->addFieldToFilter('seller_id',\Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Customer\Model\Session')->getId())
                                                                                                     ->getFirstItem();
                        
                        if($membershipData['id']){                                                                             
                            if($membershipData['remaining_number_product'] <= 0){
                                $this->messageManager->addError('Can\'t duplicate product, the remaining products number you can add is 0');    
                            }elseif(strtotime($membershipData['experi_date']) < strtotime(date("Y-m-d"))){
                                $this->messageManager->addError('Can\'t duplicate product, your membership expired');    
                            }else{
                                $newProduct = $productCopier->copy($product);
                                $this->messageManager->addSuccess(__('You duplicated the product.'));
                            }
                        }else{
                            $this->messageManager->addError('Can\'t duplicate product, please purchase new membership');    
                        }
                    }else{
                        $newProduct = $productCopier->copy($product);
                        $this->messageManager->addSuccess(__('You duplicated the product.'));
                    }
                    //End membership 
                }
                if ($redirectBack === 'draft') {
                    $approval = 3;                    
                }else{
                    $approval = 0;
                }                
                //Kien fix for save date product when edit , add
                $productIdCheck = $this->getRequest()->getParam('id');
				if(!$productIdCheck){
					$this->_eventManager->dispatch(
						'controller_action_catalog_product_savepost_entity_after',
						['controller' => $this,'action_mk'=>'add','product_id'=>$product->getId(),'product_type'=>$product->getTypeId(),'product_status'=>$product->getStatus(),'approval'=>$approval]
					);
				}else{
				    $this->_eventManager->dispatch(
						'controller_action_catalog_product_savepost_entity_after',
						['controller' => $this,'action_mk'=>'edit','product_id'=>$product->getId(),'product_type'=>$product->getTypeId(),'product_status'=>$product->getStatus(),'approval'=>$approval]
					);
				}
			} catch(\Magento\Framework\Exception\LocalizedException $e) {
				$this->messageManager->addError($e->getMessage());
			} catch(\Exception $e){
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->messageManager->addError($e->getMessage());			
			}
            if ($redirectBack === 'draft') {
                $resultRedirect->setPath('marketplace/seller/myProducts');
            } elseif ($redirectBack === 'new') {
                $resultRedirect->setPath(
                    'marketplace/*/create',
                    ['set' => $productAttributeSetId, 'type' => $productTypeId]
                );
            } elseif ($redirectBack === 'duplicate' && isset($newProduct)) {
                $customerData=$customerSession->getCustomer()->getData();
    			$_model=$this->_objectManager->create('Magebay\Marketplace\Model\Products');
    			$product_id=$newProduct->getId();
    			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                if($objectManager->create('Magebay\Marketplace\Helper\Data')->getProductApprovalRequired()){
                    $product_status = 0;
                }else{
                    $product_status = 1;
                }					
    			$_params['product_id']=$product_id;
    			$_params['status']=$product_status;			
    
    			if($customerData['entity_id']){
    				$customerId=$customerData['entity_id'];
    				$_params['user_id']=$customerId;
    			}
    			$storeid=$this->_storeManager->getStore(true)->getId();
    			$_params['store_ids']=$storeid;			
    			$_params['adminassign']=0;	
                $_params['created']=date('Y-m-d H:i:s',$this->_timezone->scopeTimeStamp());
                $_params['modified']=date('Y-m-d H:i:s',$this->_timezone->scopeTimeStamp());
    								
    			if($newProduct->getTypeId()==ConfigurableProduct::TYPE_CODE){
    				$productids=$this->getProductSimpleOfProductConfig($product_id);	
    				$_model1=$this->_objectManager->create('Magebay\Marketplace\Model\ResourceModel\Products');
    				$_arrayparam=array();
    				$_arrayparam[]=$_params;
    				foreach($productids as $key=>$val){
    					$_model2=$this->_objectManager->create('Magebay\Marketplace\Model\Products')->getCollection()->addFieldToFilter('product_id',$_params['product_id'])
                                                                                                                  ->addFieldToFilter('user_id',$_params['user_id'])
                                                                                                                  ->getFirstItem();
                        if(!$_model2){
        					$_params['product_id']=$val;
        					$_arrayparam[]=$_params;
                        }
    				}
    				$_model1->getConnection()->insertMultiple($_model1->getMainTable(), $_arrayparam);
    			}else{
    				$_model->addData($_params);
    				$_model->save();				
    			} 
                
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
                
                $resultRedirect->setPath(
					'marketplace/*/edit',
					['id' => $productId]
				);
            } elseif ($redirectBack === 'close') { 
                $resultRedirect->setPath('marketplace/seller/myProducts');
            } elseif ($redirectBack === 'submit') { 
                $resultRedirect->setPath(
					'marketplace/*/edit',
					['id' => $productId]
				);
            } else {
                $resultRedirect->setPath('marketplace/seller/myProducts');
            }
		}
		return $resultRedirect;
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
	
    /**
     * @return \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    protected function getCategoryLinkManagement()
    {
        if (null === $this->categoryLinkManagement) {
            $this->categoryLinkManagement = $this->_objectManager->get('Magento\Catalog\Api\CategoryLinkManagementInterface');
        }
        return $this->categoryLinkManagement;
    }
	
    /**
     * @param array $postData
     * @param int $productId
     * @return void
     */
    protected function handleImageProRemoveError($postData, $productId)
    {
        if (isset($postData['product']['media_gallery']['images'])) {
            $removedImagesAmount = 0;
            foreach ($postData['product']['media_gallery']['images'] as $image) {
                if (!empty($image['removed'])) {
                    $removedImagesAmount++;
                }
            }
            if ($removedImagesAmount) {
                $expectedImagesAmount = count($postData['product']['media_gallery']['images']) - $removedImagesAmount;
                $product = $this->productRepository->getById($productId);
                if ($expectedImagesAmount != count($product->getMediaGallery('images'))) {
                    $this->messageManager->addNotice(
                        __('The image cannot be removed as it has been assigned to the other image role')
                    );
                }
            }
        }
    }
}