<?php
/**
 * @Author      : David
 * @package     Messages
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/

namespace Magebay\Messages\Block\Adminhtml\Messages\Edit\Tab;
 
use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;

class Reply extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magebay\messages\Model\GridFactory
     */
    protected $replyFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
		\Magebay\Messages\Model\ReplyFactory $replyFactory,
        /*\Magento\Catalog\Model\ProductFactory $productFactory,*/
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        /*$this->productFactory = $productFactory;*/
        $this->replyFactory = $replyFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('messages_tab_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        /*$collection = $this->productFactory->create()->getCollection()->addAttributeToSelect("*");
        $this->setCollection($collection);*/
		$messages_id = $this->getRequest()->getParam('id');
		$collection = $this->replyFactory->create()->getCollection()->addFieldToFilter('messages_id',$messages_id);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'reply_id',
            [
                'header' => __('Reply Id'),
                'sortable' => true,
                'index' => 'reply_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
		$this->addColumn(
            'user_id',
            [
                'header' => __('User Id'),
                'sortable' => true,
                'index' => 'user_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
		$this->addColumn(
			'user_name',
			[
				'header' => __('User Name'),
				'index' => 'user_id',
				'filter' => false,
				'renderer' => 'Magebay\Messages\Block\Adminhtml\Grid\Column\MessagesGridReplyUserName'
			]
		);
        $this->addColumn(
            'description',
            [
                'header' => __('Message'),
                'index' => 'description'
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header' => __('created_at'),
                'index' => 'created_at'
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('magebay/messages/reply', ['_current' => true]);
    }
}