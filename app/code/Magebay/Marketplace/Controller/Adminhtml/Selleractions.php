<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Adminhtml;
/**
 * Abstract admin controller
 */
abstract class Selleractions extends \Magento\Backend\App\Action
{
    /**
     * Form session key
     * @var string
     */
	 
	const BASE_MEDIA_PATH = 'marketplace';
    protected $_formSessionKey;
    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey ;
    /**
     * Model class name
     * @var string
     */
    protected $_modelClass;
    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu;
    /**
     * Store config section key
     * @var string
     */
    protected $_configSection;
    /**
     * Request id key
     * @var string
     */
    protected $_idKey = 'id';
    /**
     * Status field name
     * @var string
     */
    protected $_statusField     = 'userstatus';
    /**
     * Save request params key
     * @var string
     */
    protected $_paramsHolder;
    /**
     * Model Object
     * @var \Magento\Framework\Model\AbstractModel
     */
	protected $_model;
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * Action execute
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $_preparedActions = array('index', 'grid', 'new', 'edit', 'save', 'delete', 'config', 'massStatus');
        $_action = $this->getRequest()->getActionName();
        if (in_array($_action, $_preparedActions)) {
            $method = '_'.$_action.'Action';
            $this->_beforeAction();
            $this->$method();
            $this->_afterAction();
        }
    }
    /**
     * Index action
     * @return void
     */
    protected function _indexAction()
    {
        if ($this->getRequest()->getParam('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->_view->loadLayout();
        $this->_setActiveMenu($this->_activeMenu);
        $title = __('Manage '.$this->_getModel(false)->getOwnTitle(true));
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_addBreadcrumb($title, $title);
        $this->_view->renderLayout();
    }
    /**
     * Grid action
     * @return void
     */
    protected function _gridAction()
    { 
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
    /**
     * New action
     * @return void
     */
    protected function _newAction()
    {
        $this->_forward('edit');
    }
    /**
     * Edit action
     * @return void
     */
    public function _editAction()
    {
        $model = $this->_getModel();
        $this->_getRegistry()->register('current_model', $model);
        $this->_view->loadLayout();
        $this->_setActiveMenu($this->_activeMenu);
        $title = $model->getOwnTitle();
        if ($model->getId()) {
            $breadcrumbTitle = __('Edit '.$title);
            $breadcrumbLabel = $breadcrumbTitle;
        } else {
            $breadcrumbTitle = __('New '.$title);
            $breadcrumbLabel = __('Create '.$title);
        }
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__($title));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getId() ? $this->_getModelName($model) : __('New '.$title)
        );
        $this->_addBreadcrumb($breadcrumbLabel, $breadcrumbTitle);
        // restore data
        $values = $this->_getSession()->getData($this->_formSessionKey, true);
        if ($this->_paramsHolder) {
            $values = isset($values[$this->_paramsHolder]) ? $values[$this->_paramsHolder] : null;
        }
        if ($values) {
            $model->addData($values);
        }
        $this->_view->renderLayout();
    }
    /**
     * Retrieve model name
     * @param  boolean $plural
     * @return string
     */
    protected function _getModelName(\Magento\Framework\Model\AbstractModel $model)
    {
        return $model->getName() ?: $model->getTitle();
    }
    /**
     * Save action
     * @return void
     */
    public function _saveAction()
    {
        $request = $this->getRequest();
        $file = $this->getRequest()->getFiles();
        if (!$request->isPost()) {
            $this->getResponse()->setRedirect($this->getUrl('*/*'));
        }
        $model = $this->_getModel();
		
		$params = $this->_paramsHolder ? $request->getParam($this->_paramsHolder) : $request->getParams();
		$model->addData($params);
		$data = $model->getData();
		
		/* prepare Company Logo image */
        $imageField = 'logoimg';
        $fileSystem = $this->_objectManager->create('Magento\Framework\Filesystem');
        $mediaDirectory = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        if (!empty($file[$imageField]) and $file[$imageField]['name'] != '' ) {
            $uploader = $this->_objectManager->create('Magento\MediaStorage\Model\File\UploaderFactory');
            $uploader = $uploader->create(['fileId' => $imageField]);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true); 
            $result = $uploader->save(
                $mediaDirectory->getAbsolutePath( Selleractions::BASE_MEDIA_PATH)
            ); 
          $model->setData($imageField,  Selleractions::BASE_MEDIA_PATH . $result['file']);
		  
        } else {
				
            if (isset($data[$imageField]) && isset($data[$imageField]['value'])) {
                if (isset($data[$imageField]['delete'])) {
                    unlink($mediaDirectory->getAbsolutePath() . $data[$imageField]['value']);
                    $model->setData($imageField, '');
                } else {
                    $model->unsetData($imageField);
                }
            } 			
        } 
		
		$imageField2 = 'bannerimg';
        $fileSystem = $this->_objectManager->create('Magento\Framework\Filesystem');
        $mediaDirectory = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        if (!empty($file[$imageField2]) and $file[$imageField2]['name'] != '' ) {
            $uploader = $this->_objectManager->create('Magento\MediaStorage\Model\File\UploaderFactory');
            $uploader = $uploader->create(['fileId' => $imageField2]);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true); 
            $result = $uploader->save(
                $mediaDirectory->getAbsolutePath( Selleractions::BASE_MEDIA_PATH)
            ); 
          $model->setData($imageField2,  Selleractions::BASE_MEDIA_PATH . $result['file']);
		  
        } else {
				
            if (isset($data[$imageField2]) && isset($data[$imageField2]['value'])) {
                if (isset($data[$imageField2]['delete'])) {
                    unlink($mediaDirectory->getAbsolutePath() . $data[$imageField2]['value']);
                    $model->setData($imageField2, '');
                } else {
                    $model->unsetData($imageField2);
                }
            } 			
        }
		
		
		/* prepare relative links */
        if ($links = $request->getPost('links')) {
            $jsHelper = $this->_objectManager->create('Magento\Backend\Helper\Js');
            $links = is_array($links) ? $links : [];
            $linkTypes = ['relatedposts', 'relatedproducts'];
            $message = '';
            $message2 = '';
            foreach ($linkTypes as $type) {
                if (isset($links[$type])) {
                    $links[$type] = $jsHelper->decodeGridSerializedInput($links[$type]);
                    //validate save product for seller
                    foreach($links[$type] as $key => $value){
                        $validateProduct = $this->_objectManager->create('Magebay\Marketplace\Model\Products')->getCollection()->addFieldToFilter('product_id',$key)->getFirstItem();
                        if($validateProduct['id'] && ($validateProduct['user_id'] != $model->getUserId())){
                            unset($links[$type][$key]);
                            $productModel = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($validateProduct['product_id']);
                            
                    		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    		$sellerModel = $objectManager->create('Magebay\Marketplace\Model\Sellers')->getCollection()->addFieldToFilter('user_id',$validateProduct['user_id'])->getFirstItem();
                    		$router = 'marketplace/sellers/edit/id/'.$sellerModel['id'];
                    		$router_admin = $this->_helper->getUrl($router, $param = [] );
                            
                            $customerModel = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($validateProduct['user_id']);
                            $message .= "Product ".$productModel->getName()." Can't Save Because this product already by seller <a href='".$router_admin."'>".$customerModel->getName()."</a></br>";
                        }else{
                            if(!$validateProduct['id']){
                                $links[$type][$key]['status'] = 1;  
                                $productModel = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($key);
                                $message2 .= "Product ".$productModel->getName()." has been saved for this seller</br>";
                            }
                        }
                    }
                    $model->setData($type.'_links', $links[$type]);
                    if($message){
                        $this->messageManager->addError($message);
                    }
                    if($message2){
                        $this->messageManager->addSuccess($message2);
                    }
                }
            }
        }
		// print_r( $links[$type] ); exit();
        try {
		
            $this->_beforeSave($model, $request);
            $model->save();
            $this->_afterSave($model, $request);
            $this->messageManager->addSuccess(__($model->getOwnTitle().' has been saved.'));
            $this->_setFormData(false);
            if ($request->getParam('back')) {
                $this->_redirect('*/*/edit', [$this->_idKey => $model->getId()]);
            } else {
                $this->_redirect('*/*');
            }
            return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError(nl2br($e->getMessage()));
            $this->_setFormData();
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving this '.strtolower($model->getOwnTitle()).'.').' '.$e->getMessage());
            $this->_setFormData();
        }
        $this->_redirect('*/*/edit', [$this->_idKey => $model->getId()]);
    }
    /**
     * Before model Save action
     * @return void
     */
    protected function _beforeSave($model, $request) {}
    /**
     * After model action
     * @return void
     */
    protected function _afterSave($model, $request) {}
    /**
     * Before action
     * @return void
     */
    protected function _beforeAction() {}
    /**
     * After action
     * @return void
     */
    protected function _afterAction() {}
    /**
     * Delete action
     * @return void
     */
    protected function _deleteAction()
    {
        $ids = $this->getRequest()->getParam($this->_idKey);
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $error = false;
        try {
            foreach($ids as $id) {
                $this->_objectManager->create($this->_modelClass)->setId($id)->delete();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $error = true;
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $error = true;
            $this->messageManager->addException($e, __('We can\'t delete '.strtolower($this->_getModel(false)->getOwnTitle()).' right now. '.$e->getMessage()));
        }
        if (!$error) {
            $this->messageManager->addSuccess(
                __($this->_getModel(false)->getOwnTitle(count($ids) > 1).' have been deleted.')
            );
        }
        $this->_redirect('*/*');
    }
    /**
     * Change status action
     * @return void
     */
    protected function _massStatusAction()
    {
        $ids = $this->getRequest()->getParam($this->_idKey);
        
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $model = $this->_getModel(false);
        $error = false;
        try {
            $status = $this->getRequest()->getParam('status');
            $statusFieldName = $this->_statusField;
            if (is_null($status)) {
                throw new \Exception(__('Parameter "Status" missing in request data.'));
            }
            if (is_null($statusFieldName)) {
                throw new \Exception(__('Status Field Name is not specified.'));
            }
            foreach($ids as $id) {
                $data = $this->_objectManager->create($this->_modelClass)
                            ->load($id)
                            ->setData($this->_statusField, $status)
                            ->save();
                if($status == 1){                            
                    $this->_objectManager->create('Magebay\Marketplace\Helper\EmailSeller')->sendApproveSellerEmail($data->getUserId());
                }else{
                    $this->_objectManager->create('Magebay\Marketplace\Helper\EmailSeller')->sendUnapproveSellerEmail($data->getUserId());
                                    
                }                                                
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $error = true;
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $error = true;
            $this->messageManager->addException($e, __('We can\'t change status of '.strtolower($model->getOwnTitle()).' right now. '.$e->getMessage()));
        }
        if (!$error) {
            $this->messageManager->addSuccess(
                __($model->getOwnTitle(count($ids) > 1).' status have been changed.')
            );
        }
        $this->_redirect('*/*');
    }
    /**
     * Go to config section action
     * @return void
     */
    protected function _configAction()
    {
        $this->_redirect('admin/system_config/edit', ['section' => $this->_configSection()]);
    }
    /**
     * Set form data
     * @return $this
     */
    protected function _setFormData($data = null)
    {
        $this->_getSession()->setData($this->_formSessionKey,
            is_null($data) ? $this->getRequest()->getParams() : $data);
        return $this;
    }
    /**
     * Get core registry
     * @return void
     */
    protected function _getRegistry()
    {
        if (is_null($this->_coreRegistry)) {
            $this->_coreRegistry = $this->_objectManager->get('\Magento\Framework\Registry');
        }
        return $this->_coreRegistry;
    }
    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed($this->_allowedKey);
    }
    /**
     * Retrieve model object
     * @return \Magento\Framework\Model\AbstractModel
     */
    protected function _getModel($load = true)
    {
    	if (is_null($this->_model)) {
    		$this->_model = $this->_objectManager->create($this->_modelClass);
            $id = (int)$this->getRequest()->getParam($this->_idKey);
		    if ($id && $load) {
		        $this->_model->load($id);
		    }
    	}
    	return $this->_model;
    }
}