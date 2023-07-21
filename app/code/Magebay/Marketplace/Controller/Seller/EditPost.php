<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Seller;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;

class EditPost extends \Magento\Framework\App\Action\Action{
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
	/**
     * @var \Magento\Customer\Model\Session
     */
	protected $_customerSession;
    
	public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Customer\Model\Session $customerSession,
		Context $context
	){
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
		parent::__construct($context);	
	}
	
	public function execute(){
		$resultRedirect = $this->resultRedirectFactory->create();
		$request = $this->getRequest();
		try{
			$params = $request->getParam('seller');					
			if (count($this->getRequest()->getFiles('seller'))) {				
				foreach($this->getRequest()->getFiles('seller') as $_itemfile=>$_itemfilevalue){
					if(!$_itemfilevalue['error']){
						try {
							$uploader = $this->_objectManager->create(
								'Magento\MediaStorage\Model\File\Uploader',
								['fileId' => 'seller['.$_itemfile.']']
							);
							$uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);

							/** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
							$imageAdapter = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();

							$uploader->addValidateCallback('market_'.$_itemfile, $imageAdapter, 'validateUploadFile');
							$uploader->setAllowRenameFiles(true);
							$uploader->setFilesDispersion(true);

							/** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
							$mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
												   ->getDirectoryRead(DirectoryList::MEDIA);
							$result = $uploader->save($mediaDirectory->getAbsolutePath(\Magebay\Marketplace\Model\Sellers::BASE_MEDIA_PATH));
							$params[$_itemfile] = \Magebay\Marketplace\Model\Sellers::BASE_MEDIA_PATH . $result['file'];
						} catch (\Exception $e) {
							if ($e->getCode() == 0) {
								$this->messageManager->addError($this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage()));
							}
							if (isset($params[$_itemfile.'_value'])) {
								if (isset($params[$_itemfile.'_delete'])) {
									$params[$_itemfile] = '';
									//$params['delete_image'] = true;
								} else if (isset($params[$_itemfile.'_value'])) {
									$params[$_itemfile] = $params[$_itemfile.'_value'];
								} else {
									$params[$_itemfile] = '';
								}
							}
						}					
					}else{	
						if (isset($params[$_itemfile.'_value'])) {
							if (isset($params[$_itemfile.'_delete'])) {
								$params[$_itemfile] = '';
								//$params['delete_image'] = true;
							} else if (isset($params[$_itemfile.'_value'])) {
								$params[$_itemfile] = $params[$_itemfile.'_value'];
							} else {
								$params[$_itemfile] = '';
							}
						}						
					}					
				}		
			}
			
			try{
				if ($this->getRequest()->isPost()) {						
					$_id = $params['sellerId'];					
					$_model = $this->_objectManager->create('Magebay\Marketplace\Model\Sellers')->load($_id);
					$_model->addData($params);
					$_model->save();
					$this->messageManager->addSuccess(__('You saved the info seller.'));					
				}
                
                $moduleManager = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Framework\Module\Manager');
                if($moduleManager->isEnabled('Magebay_SellerAttributeManagement')){
                    //save attribute seller
                    $isseller = $this->_objectManager->get('Magebay\Marketplace\Helper\Data')->checkIsSeller();
                    if($isseller){	
                        $customerSession = $this->_customerSession;
                        if($customerSession->isLoggedIn()){
                            $params = $this->getRequest()->getParam('attribute');					
                			if (count($this->getRequest()->getFiles('attribute'))) {				
                				foreach($this->getRequest()->getFiles('attribute') as $_itemfile=>$_itemfilevalue){
                					if(!$_itemfilevalue['error']){
                						try {
                							$uploader = $this->_objectManager->create(
                								'Magento\MediaStorage\Model\File\Uploader',
                								['fileId' => 'attribute['.$_itemfile.']']
                							);
                							$uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                
                							/** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
                							$imageAdapter = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
                
                							$uploader->addValidateCallback('market_'.$_itemfile, $imageAdapter, 'validateUploadFile');
                							$uploader->setAllowRenameFiles(true);
                							$uploader->setFilesDispersion(true);
                
                							/** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
                							$mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                												   ->getDirectoryRead(DirectoryList::MEDIA);
                							$result = $uploader->save($mediaDirectory->getAbsolutePath('Magebay/SellerAttributeManagement/images'));
                							$params[$_itemfile] = 'Magebay/SellerAttributeManagement/images' . $result['file'];
                						} catch (\Exception $e) {
                							if ($e->getCode() == 0) {
                								$this->messageManager->addError($this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage()));
                							}
                							if (isset($params[$_itemfile.'_value'])) {
                								if (isset($params[$_itemfile.'_delete'])) {
                									$params[$_itemfile] = '';
                									//$params['delete_image'] = true;
                								} else if (isset($params[$_itemfile.'_value'])) {
                									$params[$_itemfile] = $params[$_itemfile.'_value'];
                								} else {
                									$params[$_itemfile] = '';
                								}
                							}
                						}					
                					}else{												
                						if (isset($params[$_itemfile.'_value'])) {
                							if (isset($params[$_itemfile.'_delete'])) {
                								$params[$_itemfile] = '';
                								//$params['delete_image'] = true;
                							} else if (isset($params[$_itemfile.'_value'])) {
                								$params[$_itemfile] = $params[$_itemfile.'_value'];
                							} else {
                								$params[$_itemfile] = '';
                							}
                						}						
                					}					
                				}		
                			}
                            $value = \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\SellerAttributeManagement\Model\SellerAttributeValue')->getCollection()->addFieldToFilter('seller_id', $this->_customerSession->getId());
                            if(count($value)){                                                     
                                foreach($value as $row){
                                    $row->setValue(serialize($params));
                                    $row->save();
                                }  
                            }else{
                                $sellerattributevalue = \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\SellerAttributeManagement\Model\SellerAttributeValue');
                                $sellerattributevalue->setData('seller_id', $this->_customerSession->getId());
                                $sellerattributevalue->setData('value', serialize($params));
                                $sellerattributevalue->save();
                            }
                        }
                    }
                }	
			}catch(\Magento\Framework\Exception\LocalizedException $e){
				$this->messageManager->addError($this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage()));
			}
		}catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage()));            
        }
		return $resultRedirect->setPath('*/*/account');
	}	
}