<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class QuoteSubmitSuccess
 *
 * @category Magebay
 * @package  Magebay_Marketplace
 * @module   Marketplace
 * @author   Magebay Developer
 */
class QuoteSubmitSuccess implements ObserverInterface
{
    protected $_resource; 
	protected $_mkProduct;
    protected $_helper;

    /**
     * QuoteSubmitSuccess constructor.
     *
     * @param \Magebay\Marketplace\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
		\Magebay\Marketplace\Model\ProductsFactory $mkProduct,
        \Magebay\Marketplace\Helper\EmailSeller $helper
    ){
        $this->_resource = $resource;
        $this->_mkProduct = $mkProduct;
        $this->_helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        $items = $order->getAllVisibleItems();
        $mkProductModel = $this->_mkProduct->create();
        $seller_ids = array();
        foreach($items as $item)
		{
			$mkProductCollection = $mkProductModel->getCollection()
                                                  ->addFieldToFilter('product_id',$item->getProductId())
                                                  ->addFieldToFilter('status',1);
                                    
            //Kien 19/5/2016 - update filter seller approve        
            $tableMKuser = $this->_resource->getTableName('multivendor_user');
            $mkProductCollection->getSelect()->joinLeft(array('mk_user'=>$tableMKuser),'main_table.user_id = mk_user.user_id',array())
                                ->where('mk_user.userstatus = 1'); 
                                
            //Assign product                                                                                         
			$sellerId = 0;
            $productOption = $item->getProductOptions();
            $infoBuyRequest = $productOption['info_buyRequest'];
            if(@$infoBuyRequest['assignproduct_id']){
                $SellerAssignProduct = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magebay\SellerAssignProduct\Model\SellerAssignProduct')->load($infoBuyRequest['assignproduct_id']);
                $sellerId = $SellerAssignProduct->getSellerId();
                $multivendor_assign_product_id = $infoBuyRequest['assignproduct_id'];                    
            }else{
				if(count($mkProductCollection))
				{
					foreach($mkProductCollection as $mkProductCollect)
					{
						$sellerId = $mkProductCollect->getUserId();
                        $multivendor_assign_product_id = 0;                            
						break;
					}
				}
            }
            //end Assign product
            if($sellerId == 0)
			{
				continue;
			}
            $seller_ids[$sellerId] = $sellerId;
		}
        foreach($seller_ids as $key=>$val){
            $this->_helper->sendNewOrderEmail($order,$val);
        }
    }
}
