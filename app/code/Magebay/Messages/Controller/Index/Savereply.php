<?php
namespace Magebay\Messages\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magebay\Messages\Model\MessagesFactory;
//use Magebay\Marketplace\Helper\Email;

class Savereply extends \Magento\Framework\App\Action\Action
{
	const XML_PATH_EMAIL_TEMPLATE_FIELD  = 'marketplace/general/email_contact_vendor';
	protected $_customerFactory;
	protected $_mkHelperMail;
	protected $_MessagesFactory;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
    */
    public function __construct(
        Context $context,
		MessagesFactory $MessagesFactory,
        PageFactory $resultPageFactory
		
    ) {
        parent::__construct($context);
		$this->_MessagesFactory = $MessagesFactory;
        $this->resultPageFactory = $resultPageFactory;
    }
    
    public function execute()
    {
		$time = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\Timezone');	
		
        $post = $this->getRequest()->getPostValue();
        $model = $this->_objectManager->create('Magebay\Messages\Model\Reply');
        $model->setData('messages_id', $post['messages_id']);
        $model->setData('user_id', $post['user_id']);
        $model->setData('description', $post['message']);
        $model->setData('created_at', date('Y-m-d H:i:s',$time->scopeTimeStamp()));
        $model->save();
        
        $model2 = $this->_objectManager->create('Magebay\Messages\Model\Messages');
        $model2->load($post['messages_id']);
		$status = json_decode($model2->getData('status'), true);
		$status[ $model2->getData('user_id') ] = 'unread';
		$status[ $model2->getData('usercontact_id') ] = 'unread';
		$status[ $post['user_id'] ] = 'read';

		$model2->setStatus( json_encode($status) );
        $model2->setReplyDate(date('Y-m-d H:i:s',\Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Framework\Stdlib\DateTime\Timezone')->scopeTimeStamp()));
        $model2->save();
        $url_redirect = 'messages/index/view/id/'.$post['messages_id'];
		
		/*Send Email Info*/
		$receiver = $this->_objectManager->create('Magento\Customer\Model\Customer')->load( $post['receiver_id'] );
		$sender = $this->_objectManager->create('Magento\Customer\Model\Customer')->load( $post['user_id'] );

		$receiverName = $receiver->getFirstname(). ' ' .$receiver->getLastname();
		$receiverEmail = $receiver->getEmail();
		$senderName = $sender->getFirstname(). ' ' .$sender->getLastname();
		$senderEmail = $sender->getEmail();
		$customerSubject = $model2->getTitle();
		$customerAsk = $this->getRequest()->getParam('message','');
		$redirectUrl = $this->getRequest()->getParam('back_url','marketplace');
		if ( $this->getRequest()->getParam('redirect_url','') != '' ){ 
            $redirectUrl = $this->getRequest()->getParam('redirect_url','');
		}	
        
		/*Sender Detail*/
		$senderInfo = [
			'name' => $senderName,
			'email' => $senderEmail,
		]; 
	   
		/*Receiver Detail*/
		$receiverInfo = [
			'name' => $receiverName,
			'email' => $receiverEmail
		];
		 
		/*Assign values for your template variables*/
		$emailTempVariables['vendor_name'] = $receiverName;
		$emailTempVariables['customer_email'] = $senderEmail;
		$emailTempVariables['customer_subject'] = $customerSubject;
		$emailTempVariables['customer_content'] = $customerAsk;
		
		/* We write send mail function in helper because if we want to use same in other action then we can call it directly from helper */ 
		 
		/* call send mail method from helper or where you define it*/ 
		try{
			$tempPathFiled = self::XML_PATH_EMAIL_TEMPLATE_FIELD;
			$this->_objectManager->create('Magebay\Marketplace\Helper\Email')->sendMkEmail($tempPathFiled,$emailTempVariables,$senderInfo,$receiverInfo);
			$this->messageManager->addSuccess(__('You have been sent email contact successful.'));
			$this->_redirect($redirectUrl);
		}catch (\Exception $e) {
			$this->messageManager->addError($e->getMessage());
			$this->_redirect( $redirectUrl );
		}	 
    }
}