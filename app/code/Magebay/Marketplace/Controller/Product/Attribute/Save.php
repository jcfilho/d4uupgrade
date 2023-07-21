<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Product\Attribute;
class Save extends \Magebay\Marketplace\Controller\Product\Attribute{

	protected $_attributeFactory;
	
	protected $resultPageFactory;
	
	protected $_groupCollectionFactory;
	
	protected $_filterManager;
	
	protected $_coreRegistry;
	
    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;
	
    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory
     */
    protected $validatorFactory;	
	
	public function __construct(	
		\Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Registry $coreRegistry,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
		\Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory,
		\Magento\Framework\Filter\FilterManager $filterManager,
		\Magento\Catalog\Helper\Product $productHelper
	){
		parent::__construct($context,$customerSession,$coreRegistry,$resultPageFactory);	
		$this->resultPageFactory=$resultPageFactory;
		$this->_attributeFactory=$attributeFactory;
		$this->productHelper = $productHelper;
		$this->_groupCollectionFactory=$groupCollectionFactory;
		$this->_filterManager=$filterManager;
	}

	public function execute(){
		$data = $this->getRequest()->getPostValue();
		$__attributes = $this->_objectManager->get(
			'Magento\Catalog\Model\Product'
		)->getAttributes();
		foreach ($__attributes as $__attribute) {
			$allattrcodes = $__attribute->getEntityType()->getAttributeCodes();
		}
		if (count($allattrcodes)
			&& in_array($data['attribute_code'], $allattrcodes)
		) {
			$this->messageManager->addError(
				__('Attribute Code already exists')
			);
			return $this->resultRedirectFactory->create()->setPath(
				'*/*/new',
				['_secure' => true]
			);
		} else {
			$dataObject=array();
			$resultRedirect = $this->resultRedirectFactory->create();		
			try{
				if($data){
					$dataObject['attribute_code']=$data['attribute_code'];
					$dataObject['frontend_label'][0]=$data['attribute_label'];
					$dataObject['frontend_input']=$data['frontend_input'];
					$dataObject['is_required']=$data['is_required'];
					$dataObject['is_filterable_in_grid']=1;
					$dataObject['is_used_in_grid']=1;
					$dataObject['is_unique']=0;
					$dataObject['is_global']=1;
					$dataObject['default_value_yesno']=0;
					$dataObject['is_visible_in_grid']=1;
					$dataObject['is_searchable']=0;
					$dataObject['is_comparable']=0;
					//$dataObject['is_filterable']=1;
					//$dataObject['is_filterable_in_search']=0;
					$dataObject['is_used_for_promo_rules']=0;
					$dataObject['is_html_allowed_on_front']=1;
					$dataObject['is_visible_on_front']=0;
					$dataObject['used_in_product_listing']=0;
					$dataObject['used_for_sort_by']=0;
					$dataObject['swatch_input_type']='dropdown';
					$model=$this->_attributeFactory->create();
								
					/**
					 * @todo add to helper and specify all relations for properties
					 */
					$dataObject['source_model'] = $this->productHelper->getAttributeSourceModelByInputType(
						$data['frontend_input']
					);
					$dataObject['backend_model'] = $this->productHelper->getAttributeBackendModelByInputType(
						$data['frontend_input']
					);
					$dataObject += ['is_filterable' => 0, 'is_filterable_in_search' => 0, 'apply_to' => []];

					if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
						$dataObject['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);
					}

					$defaultValueField = $model->getDefaultValueByInput($data['frontend_input']);
					if ($defaultValueField) {
						$dataObject['default_value'] = $this->getRequest()->getParam($defaultValueField);
					}

					if (!$model->getIsUserDefined() && $model->getId()) {
						// Unset attribute field for system attributes
						unset($dataObject['apply_to']);
					}				
					
					if($data['attroptions']){
						foreach($data['attroptions'] as $key=>$item){
							if($key==$data['isdefault'][0]){
								//foreach($item as $k1=>$_v1){	
									$dataObject['default'][]='option_'.$data['isdefault'][0];
								//}							
								//continue;
							}
							foreach($item as $k=>$_v){
								switch($k){
									case 'admin':
										$dataObject['option']['value']['option_'.$key][0]=$_v;
										break;
									case 'store':
										$dataObject['option']['value']['option_'.$key][1]=$_v;
										break;									
									case 'position':
										$dataObject['option']['order']['option_'.$key]=$_v;
										break;								
									default:
										break;									
								}							
							}						
						}
					}				
					$model->addData($dataObject);
					$model->setEntityTypeId($this->_entityTypeId);
					$model->setIsUserDefined(1);				
					$model->save();
					$this->messageManager->addSuccess(__('You saved the product attribute.'));			
					
				}
			}catch(\Exception $e){			
				$this->messageManager->addException($e, __('Something went wrong while saving the attribute.'));
                //$this->_session->setAttributeData($data);
			}
			return $resultRedirect->setPath('marketplace/*/add');
		}
	}	
}