<?php
/**
 * @Author      : Dream
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Seller;

class SendmailSeller extends \Magento\Framework\App\Action\Action
{
	const XML_PATH_EMAIL_TEMPLATE_FIELD  = 'marketplace/general/email_contact_vendor';
	protected $_customerFactory;
	protected $_mkHelperMail;
	
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\CustomerFactory $customerFactory,
		\Magebay\Marketplace\Helper\Email $mkHelperMail
    )
    {
		$this->_customerFactory = $customerFactory;
		$this->_mkHelperMail = $mkHelperMail;
        parent::__construct($context);
    }

    public function execute()
    {
		$moduleManager = $this->_objectManager->create('Magento\Framework\Module\Manager');	
		if( $moduleManager->isEnabled('Magebay_Messages') and $this->getRequest()->getParam('customer_id','') != '' )
		{
			$time = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\Timezone');	
			$messages = $this->_objectManager->create('Magebay\Messages\Model\Messages');
			//$messages->load($MessagesId);
			$params['user_id'] = $this->getRequest()->getParam('seller_id','');
			$params['usercontact_id'] = $this->getRequest()->getParam('customer_id','');
			$params['product_id'] = $this->getRequest()->getParam('product_id','');
			$params['order_id'] = $this->getRequest()->getParam('order_id','');
			$params['title'] = $this->getRequest()->getParam('subject','');
			$params['description'] = $this->getRequest()->getParam('ask','');
			$params['created_at'] = date('Y-m-d H:i:s',$time->scopeTimeStamp());
			$params['reply_date'] = date('Y-m-d H:i:s',$time->scopeTimeStamp());
			$status[ $this->getRequest()->getParam('seller_id','') ] = 'unread';
			$status[ $this->getRequest()->getParam('customer_id','') ] = 'read';
			//$new_json = json_encode( $json );
			$messages->addData($params);
			$messages->setStatus( json_encode($status) );
            $messages->save();
		}

		$sellerId = $this->getRequest()->getParam('seller_id',0);
		$sellerId = (int)$sellerId;
		if($sellerId == 0)
		{
			$this->_redirect('marketplace');
		}
		$customerModel = $this->_customerFactory->create();
		$customer = $customerModel->load($sellerId);
		if(!$customer->getId()){
			$this->_redirect('marketplace');
		}else{
			$vendorName = $customer->getFirstname(). ' ' .$customer->getLastname();
			$vendorEmail = $customer->getEmail();
			$customerEmail = $this->getRequest()->getParam('email','');
			$customerSubject = $this->getRequest()->getParam('subject','');
			$customerAsk = $this->getRequest()->getParam('ask','');
			$redirectUrl = $this->getRequest()->getParam('back_url','marketplace');
			if ( $this->getRequest()->getParam('redirect_url','') != '' ){ 
                $redirectUrl = $this->getRequest()->getParam('redirect_url','');
			}
            
            /* Sender Detail  */
			$senderName = 'New contact';
			if ( $this->getRequest()->getParam('customer_id','') != '' ) {
				$senderDetail = $customerModel->load( $this->getRequest()->getParam('customer_id','') );
				$senderName = $senderDetail->getFirstname(). ' ' .$senderDetail->getLastname();
			}
			$senderInfo = [
				'name' => $senderName,
				'email' => $customerEmail,
			]; 
           
            /* Receiver Detail  */
			$receiverInfo = [
    			'name' => $vendorName,
    			'email' => $vendorEmail
			];
			 
			/* Assign values for your template variables  */
			$emailTempVariables['vendor_name'] = $vendorName;
			$emailTempVariables['customer_email'] = $customerEmail;
			$emailTempVariables['customer_subject'] = $customerSubject;
			$emailTempVariables['customer_content'] = $customerAsk;
			if ( @$params['order_id'] != '' ) { 
    			$order_detail = $this->_objectManager->create('Magento\Sales\Model\Order')->load( $params['order_id'] );
    			$emailTempVariables['content_related'] = 'Order ID : '.$order_detail->getIncrementId();
			} elseif ( @$params['product_id'] != '' ) { 
    			$product_detail = $this->_objectManager->create('Magento\Catalog\Model\Product')->load( $params['product_id'] );
    			$emailTempVariables['content_related'] = 'Product Name: '.$product_detail->getName();
			}
            
			/* We write send mail function in helper because if we want to use same in other action then we can call it directly from helper */ 
			/* call send mail method from helper or where you define it*/ 
			try{
				$tempPathFiled = self::XML_PATH_EMAIL_TEMPLATE_FIELD;
				$this->_mkHelperMail->sendMkEmail($tempPathFiled,$emailTempVariables,$senderInfo,$receiverInfo);
				$this->messageManager->addSuccess(__('You have been sent email contact successful.'));
				$this->_redirect($redirectUrl);
			}catch (\Exception $e) {
				$this->messageManager->addError($e->getMessage());
				$this->_redirect( $redirectUrl );
			}
		}
    } 
}