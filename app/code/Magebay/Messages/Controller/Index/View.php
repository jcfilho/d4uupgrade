<?php
namespace Magebay\Messages\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magebay\Messages\Model\MessagesFactory;

class View extends \Magento\Framework\App\Action\Action
{
	protected $_messagesFactory;
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
		$this->_messagesFactory = $MessagesFactory;
        $this->resultPageFactory = $resultPageFactory;
    }
    
    public function execute()
    {
		$customer = $this->_objectManager->create('Magento\Customer\Model\Session');	
		if ( $customer->getId() < 1 )	{ 
            $this->_redirect('customer/account/login');
		}
		
		$MessagesId = $this->getRequest()->getParam('id');
		$model = $this->_messagesFactory->create();
		$messages = $model->load($MessagesId);
		
		$status = json_decode($model->getData('status'), true);
		$status[ $customer->getId() ] = 'read';
		$model->setStatus( json_encode($status) );
        $model->save();
		
		$this->_objectManager->get('Magento\Framework\Registry')->register('MessagesData', $messages);
 
        $pageFactory = $this->resultPageFactory->create();
		$pageFactory->getConfig()->getTitle()->set( 'Messages' );
		
		/* $breadcrumbs = $pageFactory->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb('home',
            [
                'label' => __('Home Page'),
                'title' => __('Home'),
                'link' => $this->_url->getUrl('')
            ]
        );
        $breadcrumbs->addCrumb('getDatabase',
            [
                'label' => __('Messages'),
                'title' => __('Messages'),
                'link' => $this->_url->getUrl('messages')
            ]
        );
         $breadcrumbs->addCrumb('Magebaynew',
            [
                'label' => $messages->getTitle(),
                'title' => $messages->getTitle()
            ]
        ); */
		
		$this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}