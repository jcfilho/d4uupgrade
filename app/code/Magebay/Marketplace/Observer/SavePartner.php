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
use Magento\Store\Model\ScopeInterface;

class SavePartner implements ObserverInterface
{
	protected $_mkProduct;
	protected $_scopeConfig;
	protected $_saleslist;
    protected $_sellerpartner;  
    protected $_mkCoreOrder;  
    protected $_objectmanager;
	
    public function __construct(
		\Magebay\Marketplace\Model\ProductsFactory $mkProduct,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magebay\Marketplace\Model\SaleslistFactory  $saleslist,
        \Magebay\Marketplace\Model\PartnerFactory  $partner,
        \Magento\Sales\Model\OrderFactory $mkCoreOrder,
        \Magento\Framework\ObjectManagerInterface $objectmanager  
    )
    {
        $this->_mkProduct = $mkProduct;
        $this->_scopeConfig = $scopeConfig;
        $this->_saleslist = $saleslist;
        $this->_sellerpartner = $partner;
        $this->_mkCoreOrder = $mkCoreOrder;   
        $this->_objectmanager = $objectmanager;  
    }

    //Action for complete order
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $moduleManager = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Framework\Module\Manager');
        $sellerids = array();                
        if($order->getState() == 'complete'){
    		$items = $order->getAllVisibleItems();
    		$mkProductModel = $this->_mkProduct->create();
    		$percentCommision = $this->_scopeConfig->getValue('marketplace/general/percent',ScopeInterface::SCOPE_STORE); 
    		if(count($items))
    		{
                $advanced_commissions = null;
    			foreach($items as $item)
    			{
                    //Membership 
                    if($moduleManager->isEnabled('Magebay_SellerMembership') && \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\Marketplace\Helper\Data')->getSellerMembershipIsEnabled()){
                        if($item->getProductType() == 'membership'){
                            $membership = \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\SellerMembership\Model\Membership')->getCollection()->addFieldToFilter('product_id',$item->getProductId())->getFirstItem();
                            $membership_data = $membership->getData();
                            $params['seller_id'] = $order->getCustomerId();
                            $params['membership_id'] = $membership_data['id'];
                            $params['total_number_product'] = $membership_data['number'];
                            $params['remaining_number_product'] = $membership_data['number'];
                            $params['created_at'] = date("Y-m-d",time());
                            $newDate = strtotime($params['created_at'] . '+ '.$membership_data['time'].'days');
                            $params['experi_date'] = date("Y-m-d", $newDate);
                            $params['paid_status'] = 1;
                            $params['paid_total'] = $membership_data['fee'];
                            $params['status'] = 1;
                            $seller_membership = \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\SellerMembership\Model\SellerMembership')
                                                                      ->getCollection()
                                                                      ->addFieldToFilter('seller_id',$params['seller_id'])
                                                                      //->addFieldToFilter('membership_id',$params['membership_id'])
                                                                      ->getFirstItem();                                                                 
                            if($seller_membership['id']){
                                $seller_membership_old = \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\SellerMembership\Model\SellerMembership')->load($seller_membership['id']);
                                unset($params['remaining_number_product']);
                                $seller_membership_old->addData($params);
                                $seller_membership_old->save();
                            }else{
                                $seller_membership_new = \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\SellerMembership\Model\SellerMembership');
                                $seller_membership_new->addData($params);
                                $seller_membership_new->save();
                            }
                        }
                    }
                    //End membership 
                    
                    if($item->getStatus() == 'Canceled') continue;
    				$_product = $item->getProduct();
                    
                    //Assign product  
                    if($moduleManager->isEnabled('Magebay_SellerAssignProduct')){
                        $productOption = $item->getProductOptions();
                        $infoBuyRequest = $productOption['info_buyRequest'];
                        if(@$infoBuyRequest['assignproduct_id']){
                            $mkProductCollection = $this->_saleslist->create()->getCollection()
                                                                          ->addFieldToFilter('orderid',$order->getId())
                                                                          ->addFieldToFilter('prodid',$item->getProductId())
                                                                          ->addFieldToFilter('multivendor_assign_product_id',$infoBuyRequest['assignproduct_id']);
                        }else{
                            $mkProductCollection = $this->_saleslist->create()->getCollection()
                                                                          ->addFieldToFilter('orderid',$order->getId())
                                                                          ->addFieldToFilter('prodid',$item->getProductId())
                                                                          ->addFieldToFilter('multivendor_assign_product_id',0);
                        }
    				}else{
    				    $mkProductCollection = $this->_saleslist->create()->getCollection()
                                                                          ->addFieldToFilter('orderid',$order->getId())
                                                                          ->addFieldToFilter('prodid',$item->getProductId());
    				}
                    //end Assign product    
                                                                 
    				$sellerId = 0;
    				if(count($mkProductCollection))
    				{
    					foreach($mkProductCollection as $mkProductCollect)
    					{
    						$sellerId = $mkProductCollect->getSellerid();
    						break;
    					}
    				}                    
    				if($sellerId == 0)
    				{
    					continue;
    				}
                    //SellerCoupon
                    $sellerids[$sellerId] = $sellerId;
                    //end SellerCoupon
                    
                    $data = $mkProductCollection->getFirstItem();  
                    $totalamount = $data['totalamount'];
    				$totalcommision = $data['totalcommision'];
    				$actualparterprocost = $data['actualparterprocost'];
                    //Change status order => complete to pay for sale list
            		$selesListModel = $this->_saleslist->create()->getCollection()
                                                                 ->addFieldToFilter('orderid',$order->getId())
                                                                 ->addFieldToFilter('prodid',$item->getProductId())
                                                                 ->addFieldToFilter('sellerid',$sellerId)
                                                                 ->addFieldToFilter('proprice',$item->getBasePrice());
    				foreach($selesListModel as $row){
                    	$row->setOrderStatus($order->getState());
                        $row->save();
                    }  
                    //action for partner seller
                    $partnerOldModel = $this->_sellerpartner->create()->getCollection()->addFieldToFilter('sellerid',$sellerId)->getFirstItem();
                    if($partnerOldModel['sellerid']){
                        //save old data
                        $totalSale = $partnerOldModel['totalsale'] + $totalamount;
                        $totalRemain = $partnerOldModel['amountremain']+$actualparterprocost;
                        $commission = $partnerOldModel['commission'] + $totalcommision;
                        $partnerNewModel = $this->_sellerpartner->create();
                        $partnerNewModel->load($partnerOldModel['id']);
                        $partnerNewModel->setTotalsale($totalSale);
                        $partnerNewModel->setAmountremain($totalRemain);
                        $partnerNewModel->setCommission($commission);
                        $partnerNewModel->save();
                    }else{
                        //save new data
                        $partnerNewModel = $this->_sellerpartner->create();
                        $partnerNewModel->setSellerid($sellerId);
                        $partnerNewModel->setTotalsale($totalamount);
                        $partnerNewModel->setAmountreceived(0);
                        $partnerNewModel->setAmountpaid(0);
                        $partnerNewModel->setAmountremain($actualparterprocost);
                        $partnerNewModel->setCommission($totalcommision);
                        $partnerNewModel->save();
                    }
    			}
                //SellerCoupon
                if($moduleManager->isEnabled('Magebay_SellerCoupon')){
                    foreach($sellerids as $id){
                        $sellerCoupon = \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\SellerCoupon\Model\SellerCoupon')
                                                                                           ->getCollection()
                                                                                           ->addFieldToFilter('seller_id',$id)
                                                                                           ->addFieldToFilter('order_id',$order->getId())
                                                                                           ->getFirstItem();
                        if($sellerCoupon->getId()){
                            $partnerModel = $this->_sellerpartner->create()->getCollection()->addFieldToFilter('sellerid',$sellerCoupon->getSellerId())->getFirstItem();
                            if($partnerModel['sellerid']){
                                //save data coupon
                                $sellerCouponOld = \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\SellerCoupon\Model\SellerCoupon')->load($sellerCoupon->getId());
                                $sellerCouponOld->setOrderStatus($order->getStatus());
                                $sellerCouponOld->save();
                                //save data partner
                                $totalRemain = $partnerModel['amountremain'] - $sellerCoupon->getSellerCouponPrice();
                                $totalDiscount = $partnerModel['discount'] + $sellerCoupon->getSellerCouponPrice();
                                $partnerNewModel = $this->_sellerpartner->create();
                                $partnerNewModel->load($partnerModel['id']);
                                $partnerNewModel->setAmountremain($totalRemain);
                                $partnerNewModel->setDiscount($totalDiscount);
                                $partnerNewModel->save();
                            }
                        }
                    }
                }
                //end SellerCoupon                  
    		}
        }else{
            $items = $order->getAllVisibleItems();
    		if(count($items))
    		{
    			foreach($items as $item)
    			{
    				$_product = $item->getProduct();
                                                            
    				//Assign product  
                    if($moduleManager->isEnabled('Magebay_SellerAssignProduct')){
                        $productOption = $item->getProductOptions();
                        $infoBuyRequest = $productOption['info_buyRequest'];
                        if(@$infoBuyRequest['assignproduct_id']){
                            $mkProductCollection = $this->_saleslist->create()->getCollection()
                                                                          ->addFieldToFilter('orderid',$order->getId())
                                                                          ->addFieldToFilter('prodid',$item->getProductId())
                                                                          ->addFieldToFilter('multivendor_assign_product_id',$infoBuyRequest['assignproduct_id']);
                        }else{
                            $mkProductCollection = $this->_saleslist->create()->getCollection()
                                                                          ->addFieldToFilter('orderid',$order->getId())
                                                                          ->addFieldToFilter('prodid',$item->getProductId())
                                                                          ->addFieldToFilter('multivendor_assign_product_id',0);
                        }
    				}else{
    				    $mkProductCollection = $this->_saleslist->create()->getCollection()
                                                                          ->addFieldToFilter('orderid',$order->getId())
                                                                          ->addFieldToFilter('prodid',$item->getProductId());
    				}
                    //end Assign product 
                                                              
    				$sellerId = 0;
    				if(count($mkProductCollection))
    				{
    					foreach($mkProductCollection as $mkProductCollect)
    					{
    						$sellerId = $mkProductCollect->getSellerid();
    						break;
    					}
    				}                    
    				if($sellerId == 0)
    				{
    					continue;
    				}
            		$selesListModel = $this->_saleslist->create()->getCollection()
                                                                 ->addFieldToFilter('orderid',$order->getId())
                                                                 ->addFieldToFilter('prodid',$item->getProductId())
                                                                 ->addFieldToFilter('sellerid',$sellerId)
                                                                 ->addFieldToFilter('proprice',$item->getBasePrice());
                    $order1 = $this->_mkCoreOrder->create()->load($order->getId());  

    				foreach($selesListModel as $row){
                    	$row->setOrderStatus($order1->getStatus());
                        $row->save();
                    }  
    			}
    		}
        }
    }
}
