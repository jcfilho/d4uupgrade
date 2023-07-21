<?php
namespace Magebay\Messages\Block;
class Order extends \Magento\Framework\View\Element\Template
{
    protected $_gridFactory; 
    protected $_coreRegistry; 
	protected $_messagesFactory;
     
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magebay\Messages\Model\ReplyFactory $gridFactory,
        \Magento\Framework\Registry $coreRegistry,
		\Magebay\Messages\Model\MessagesFactory $MessagesFactory,
        array $data = []
    ) {
        $this->_gridFactory = $gridFactory;
        $this->_coreRegistry = $coreRegistry;
		$this->_messagesFactory = $MessagesFactory;
        parent::__construct($context, $data);
        
        //get collection of data 
		$order_id = $this->getRequest()->getParam('order_id');
		$messages = $this->_messagesFactory->create()->getCollection()->addFieldToFilter('order_id', $order_id );
		$messages_id = $messages->getLastItem()->getData('messages_id');
        $collection = $this->_gridFactory->create()->getCollection()->addFieldToFilter('messages_id',$messages_id );
        $collection->setOrder('reply_id', 'DESC' );
        $this->setCollection($collection);
        $this->pageConfig->getTitle()->set(__('Messages'));
    }
  
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            // create pager block for collection 
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'Magebay.messages.record.pager'
            )->setCollection(
                $this->getCollection() // assign collection to pager
            );
            $this->setChild('pager', $pager);// set pager block in layout
        }
        return $this;
    }
	  
    /**
     * @return string
     */
    // method for get pager html
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }  
	
	public function getMessagesId( $order_id )
    {
		//get collection of data 
		$messages = $this->_messagesFactory->create()->getCollection()->addFieldToFilter('order_id', $order_id );
		$messages_id = $messages->getLastItem()->getData('messages_id');	
        return $messages_id;
    }
	
	public function getMessagesInformation( $order_id )
    {
		$messages = $this->_messagesFactory->create()->getCollection()
                                                     ->addFieldToFilter('order_id', $order_id )
                                                     ->addFieldToFilter('user_id', \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Customer\Model\Session')->getId() );
		$messages_detail = $messages->getLastItem();	
        return $messages_detail;
    }
}