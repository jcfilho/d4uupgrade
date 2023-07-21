<?php
namespace Magebay\Messages\Controller\Adminhtml\Messages;
class Reply extends \Magebay\Messages\Controller\Adminhtml\Messages
{
    /**
     * View related products action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
		$model = $this->_getModel();
        $this->_getRegistry()->register('current_model', $model);
        $this->_view->loadLayout()
             ->getLayout()
             ->getBlock('messages.messages.edit.tab.reply');
             //->setMessagesReply($this->getRequest()->getPost('messages_reply', null));
        $this->_view->renderLayout(); 
	}
}