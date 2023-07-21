<?php
namespace Magebay\Marketplace\Controller\Adminhtml\Sellers;
class RelatedProducts extends \Magebay\Marketplace\Controller\Adminhtml\Sellers
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
            ->getBlock('marketplace.sellers.edit.tab.relatedproducts')
            ->setProductsRelated($this->getRequest()->getPost('products_related', null));
 
        $this->_view->renderLayout();
    }
}