<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Seller;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory; 
class Pay extends \Magento\Framework\App\Action\Action{

	protected $_resultJsonFactory;
	
	public function __construct(	
		Context $context,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
	){
		parent::__construct($context);	
		$this->_resultJsonFactory = $resultJsonFactory;
	}
	
	public function execute(){		
		$data = $this->getRequest()->getPost();
        $sellerId = $data['sellerid']; 
        $comment = $data['comment'];
        $oldUrl = $data['old_url'];
		$this->_redirect($oldUrl);        
	}
}