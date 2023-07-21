<?php
namespace Magebay\Messages\Block;
class View extends \Magento\Framework\View\Element\Template
{
    protected $_gridFactory; 
    protected $_coreRegistry; 
     
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magebay\Messages\Model\ReplyFactory $gridFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_gridFactory = $gridFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
        
        //get collection of data 
		$messages_id = $this->getRequest()->getParam('id');
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
	
	public function getMessagesInformation()
    {
        return $this->_coreRegistry->registry('MessagesData');
    }
}