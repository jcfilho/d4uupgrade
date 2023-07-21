<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Product;

class SaveProductType extends \Magebay\Marketplace\Controller\Product\Account{
	
	protected $resultPageFactory;	
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
	){
		parent::__construct($context, $customerSession);
		$this->resultPageFactory=$resultPageFactory;		
	}
	
	public function execute(){
		$resultRedirect = $this->resultRedirectFactory->create();
		$set=(int)$this->getRequest()->getParam('set');
		$type=$this->getRequest()->getParam('type');
		return $resultRedirect->setPath('marketplace/*/create',array('set'=>$set,'type'=>$type));
	}
}