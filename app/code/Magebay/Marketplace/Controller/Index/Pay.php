<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Index;
 
use Magento\Framework\App\Action\Context;

class Pay extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }
 
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if($data['type'] == 'complete'){
            $sellerId = $data['seller_id']; 
            $tranId = $data['tran_id']; 
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $transaction = $objectManager->create('Magebay\Marketplace\Model\Transactions')->load($tranId);
            
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $partnerOldModel = $objectManager->create('Magebay\Marketplace\Model\Partner')->getCollection()->addFieldToFilter('sellerid',$sellerId)->getFirstItem();
            
            $partnerNewModel = $objectManager->create('Magebay\Marketplace\Model\Partner')->load($partnerOldModel['id']);
            $partnerNewModel->setAmountreceived($partnerOldModel['amountreceived']+$transaction->getTransactionAmount());
            $partnerNewModel->setAmountpaid($transaction->getTransactionAmount());
            $partnerNewModel->setAmountremain($partnerOldModel['amountremain'] - $transaction->getTransactionAmount());
            $partnerNewModel->save();
            
            $transaction->setPaidStatus(2);
            $transaction->setAdminComment($data['note']);
            $transaction->save();
            $this->messageManager->addSuccess(__('The transaction has been completed'));
            //send mail
            $data['seller_id'] = $sellerId;  
            $data['payment_id'] = $transaction->getPaymentId();
            $data['payment_email'] = $transaction->getPaymentEmail();
            $data['payment_additional'] = $transaction->getPaymentAdditional();
            $data['transaction_amount'] = $transaction->getTransactionAmount();
            $data['amount_paid'] = $transaction->getAmountPaid();
            $data['amount_fee'] = $transaction->getAmountFee();
            $data['status'] = 'Completed';    
                             
            $this->_objectManager->create('Magebay\Marketplace\Helper\EmailSeller')->sendCompleteWithdrawEmailToSeller($data);
            $this->_objectManager->create('Magebay\Marketplace\Helper\EmailSeller')->sendCompelteWithdrawEmailToAdmin($data);  
        }else{
            $sellerId = $data['seller_id']; 
            $tranId = $data['tran_id']; 
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $transaction = $objectManager->create('Magebay\Marketplace\Model\Transactions')->load($tranId);
            
            $transaction->setPaidStatus(3);
            $transaction->setAdminComment($data['note']);
            $transaction->save();
            $this->messageManager->addSuccess(__('The transaction has been canceled'));
            //send mail
            $data['seller_id'] = $sellerId;  
            $data['payment_id'] = $transaction->getPaymentId();
            $data['payment_email'] = $transaction->getPaymentEmail();
            $data['payment_additional'] = $transaction->getPaymentAdditional();
            $data['transaction_amount'] = $transaction->getTransactionAmount();
            $data['amount_paid'] = $transaction->getAmountPaid();
            $data['amount_fee'] = $transaction->getAmountFee(); 
            $data['status'] = 'Canceled';    
                             
            $this->_objectManager->create('Magebay\Marketplace\Helper\EmailSeller')->sendCompleteWithdrawEmailToSeller($data);
            $this->_objectManager->create('Magebay\Marketplace\Helper\EmailSeller')->sendCompelteWithdrawEmailToAdmin($data);  
        }
        $oldUrl = $data['old_url'];
        $this->_redirect($oldUrl);
    }
}
 