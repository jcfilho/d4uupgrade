<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Product;
 
class Edit extends \Magebay\Marketplace\Controller\Product\Account {
	
    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;
	
	/**
	 * @param \Magento\Framework\App\Action\Context $context
	 * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
	 */
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
	){
		parent::__construct($context, $customerSession);
		$this->resultForwardFactory = $resultForwardFactory;
	}
	
	public function execute(){
		$resultForward = $this->resultForwardFactory->create();
		
        if($id=$this->getRequest()->getParam('id')){
            $resultForward->setController('product');
			$param = array('id' => $id);
			if($set = $this->getRequest()->getParam('set')) {
				$param['set'] = $set;
			}
			$resultForward->setParams($param);
			$resultForward->forward('create');
        } else {
            $resultForward->forward('noroute');
		}
        return $resultForward;
	}
}