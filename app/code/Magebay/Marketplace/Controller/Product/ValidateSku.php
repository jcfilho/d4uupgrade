<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Product;

use Magento\Catalog\Model\ProductFactory;

class ValidateSku extends \Magebay\Marketplace\Controller\Product\Account{
    
	
    /** @var \Magento\Catalog\Model\ProductFactory */
    protected $productFactory;
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		ProductFactory $productFactory
	){
		parent::__construct($context, $customerSession);
		$this->productFactory = $productFactory;
	}
	
	public function execute(){
		$result=array();
		$result['status']=false;
		$sku=$this->getRequest()->getPost('sku');
		if($sku){
			$result['message']=$sku.' Available';
			$_id=$this->productFactory->create()->getIdBySku($sku);
			if($_id){
				$result['status']=true;
				$result['message']=$sku.' Already Exist';
			}
		}
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );			
	}
}