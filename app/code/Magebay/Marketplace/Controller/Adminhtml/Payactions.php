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
abstract class Payactions extends \Magento\Backend\App\Action
{
    protected $_formSessionKey;
    protected $_allowedKey;
    protected $_modelClass;
    protected $_activeMenu;
    protected $_configSection;
    protected $_idKey = 'id';
    protected $_statusField = 'status';
    protected $_paramsHolder;
	protected $_model;
    protected $_coreRegistry = null;

    /**
     * Action execute
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
		$data = $this->getRequest()->getParams();
        $oldUrl = $data['old_url'];
        $sellerId = $data['sellerid']; 
        $amount_pay = $data['amount_pay'];
        $comment = $data['comment'];
        $table_id = @$data['table_id'];
        $RandomString = $this->generateRandomString(4);
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$transactions = $objectManager->create('Magebay\Marketplace\Model\Transactions');
        $time = $objectManager->create('\Magento\Framework\Stdlib\DateTime\Timezone');
        if($data['type'] == 'all_items'){
            //save data for partner seller
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $partnerOldModel = $objectManager->create('Magebay\Marketplace\Model\Partner')->getCollection()->addFieldToFilter('sellerid',$sellerId)->getFirstItem();
            
            $partnerNewModel = $objectManager->create('Magebay\Marketplace\Model\Partner')->load($partnerOldModel['id']);
            $partnerNewModel->setAmountreceived($partnerOldModel['amountreceived']+$amount_pay);
            $partnerNewModel->setAmountpaid($amount_pay);
            $partnerNewModel->setAmountremain($partnerOldModel['amountremain'] - $amount_pay);
            $partnerNewModel->save();
            
            //save data for transaction seller
    		$dataTransactionSave = array(
    			'seller_id'=>$sellerId,
    			'transaction_id'=>$RandomString,
                'payment_id'=>1,
    			'payment_email'=>'',
                'payment_additional'=>'',
    			'transaction_amount'=>$amount_pay,
    			'amount_paid'=>$amount_pay,
    			'amount_fee'=>0,
    			'commision'=>'',
    			'admin_comment'=>$comment,
    			'created_at'=>date('Y-m-d H:i:s',$time->scopeTimeStamp()),
                'paid_status'=>2
    		);
            
            //Cancel all the payment pending
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $TransactionsModel = $objectManager->create('Magebay\Marketplace\Model\Transactions')->getCollection()
                                                                                               ->addFieldToFilter('seller_id',$sellerId)
                                                                                               ->addFieldToFilter('paid_status',1);
            foreach($TransactionsModel as $row){
            	$row->setPaidStatus(3);
                $row->setAdminComment('Cancel this payment by Transaction ID : '.$RandomString);
                $row->save();
            } 
            
            //save data for sales list
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $partnerOldModel = $objectManager->create('Magebay\Marketplace\Model\Saleslist')->getCollection()
                                                                                            ->addFieldToFilter('sellerid',$sellerId)
                                                                                            ->addFieldToFilter('order_status','complete')
                                                                                            ->addFieldToFilter('paidstatus','0');
            foreach($partnerOldModel as $row){
            	$row->setPaidstatus(1);
				$row->setTransid($RandomString);
                $row->save();
            }  
        }else{
            //save data for partner seller
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $partnerOldModel = $objectManager->create('Magebay\Marketplace\Model\Partner')->getCollection()->addFieldToFilter('sellerid',$sellerId)->getFirstItem();
            
            $partnerNewModel = $objectManager->create('Magebay\Marketplace\Model\Partner')->load($partnerOldModel['id']);
            $partnerNewModel->setAmountreceived($partnerOldModel['amountreceived']+$amount_pay);
            $partnerNewModel->setAmountpaid($amount_pay);
            $partnerNewModel->setAmountremain($partnerOldModel['amountremain'] - $amount_pay);
            $partnerNewModel->save();
            
            //save data for transaction seller
    		$dataTransactionSave = array(
    			'seller_id'=>$sellerId,
    			'transaction_id'=>$RandomString,
    			'transonline_id'=>'',
    			'transaction_amount'=>$amount_pay,
    			'amount_paid'=>'',
    			'amount_remain'=>'',
    			'commision'=>'',
    			'admin_comment'=>$comment,
    			'created_at'=>date('Y-m-d H:i:s',$time->scopeTimeStamp()),
    		);
            
            //save data for sales list
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $partnerOldModel = $objectManager->create('Magebay\Marketplace\Model\Saleslist')->load($table_id);
        	$partnerOldModel->setPaidstatus(1);
			$partnerOldModel->setTransid($RandomString);
            $partnerOldModel->save();
        }
        try 
        {
            $transactions->setData($dataTransactionSave)->save();
            $okSave = true;
            $this->messageManager->addSuccess(__('You have been paid $'.$amount_pay));
            $this->_redirect($oldUrl);
		}
		catch (\Exception $e) {
			$this->messageManager->addError($e->getMessage());
			$this->_redirect($oldUrl);
		}	
		$this->_redirect($oldUrl);  
    }
    
    //create random id
    public function generateRandomString($length)
	{
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
	    
		$randomString1 = '';
		$randomString2 = '';
		for ($i = 0; $i < $length; $i++) {
            $randomString1 .= $characters[rand(0, $charactersLength - 1)];
		}
		for ($i = 0; $i < $length; $i++) {
            $randomString2 .= $characters[rand(0, $charactersLength - 1)];
		}
        
        $str = 'MK';
		$str = $str.'-'.$randomString1.'-'.$randomString2;
		
	    return $str;
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
}