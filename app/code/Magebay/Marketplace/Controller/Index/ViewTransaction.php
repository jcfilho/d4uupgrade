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

class ViewTransaction extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }
 
    public function execute()
    {
        $html = $this->_objectManager->create('\Magento\Framework\View\LayoutInterface')
            ->createBlock('Magebay\Marketplace\Block\Transactionlist')
            ->setTranId($this->getRequest()->getParam('tran_id'))
            ->setTemplate('seller/view_pay.phtml')
            ->toHtml();
        $this->getResponse()->appendBody($html); 
    }
}
 