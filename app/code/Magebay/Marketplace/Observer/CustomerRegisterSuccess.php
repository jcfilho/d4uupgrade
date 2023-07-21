<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use \Magento\Framework\App\RequestInterface;

class CustomerRegisterSuccess implements ObserverInterface
{    
    protected $_messageManager;  
    protected $_scopeConfig;
	protected $_objectManager;
	protected $_request;	
	protected $_customerCollectionFactory;
    protected $inlineTranslation;

    /**
     * OrderPlaceAfter constructor.
     *     
     * @param \Magento\Framework\Mail\Template\TransportBuilder   $transportBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface  $scopeConfig
     * @param \Magento\Framework\Translate\Inline\StateInterface  $inlineTranslation          
     * @param array                                               $data
     */
    public function __construct(        
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,        
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
		RequestInterface $request,
		ObjectManagerInterface $objectManager,        
        array $data = []
    )
    {        
        $this->_transportBuilder = $transportBuilder;        
        $this->_scopeConfig = $scopeConfig;
        $this->inlineTranslation = $inlineTranslation;        
        $this->_messageManager = $messageManager;        
        $this->_request = $request;        
        $this->_customerCollectionFactory = $customerCollectionFactory;        
		$this->_objectManager = $objectManager;        
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {		
		try{					
			$_data=$this->_request->getParams();
            if(@$_data['is_seller']){
    			$_customerconllection=self::getCustomerByEmail($_data['email']);
    			$_customer=$_customerconllection->getData();
    			$_newdata['email']=$_data['email'];
    			$_newdata['name']=$_data['firstname'].$_data['lastname'];
    			$_newdata['is_vendor']=$_data['is_seller'];
                $_newdata['storetitle']=$_data['storetitle'];
                $_newdata['contactnumber']=$_data['contactnumber'];                                
    			$_newdata['storeurl']=$_data['shopurl'];
    			$_newdata['user_id']=$_customer[0]['entity_id'];
    			$_newdata['stores_id']=$_customer[0]['store_id'];
    			$_newdata['created']=$_customer[0]['created_at'];
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                if($objectManager->create('Magebay\Marketplace\Helper\Data')->getSellerApprovalRequired()){
                    $status = 0;
                }else{
                    $status = 1;
                }
                $_newdata['userstatus']=$status;
    			$_model=$this->_objectManager->create('Magebay\Marketplace\Model\Sellers');
    			$_model->addData($_newdata);
    			$_model->save();
                //send mail to admin
                $this->_objectManager->create('Magebay\Marketplace\Helper\EmailSeller')->sendRegisterSellerEmail($_customer);
            }
		}catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_messageManager->addError(nl2br($e->getMessage()));            
        } catch (\Exception $e) {
            $this->_messageManager->addException($e, __('Something went wrong while saving this .').' '.$e->getMessage());            
        }		
    }
	
	public function getCustomerByEmail($email){
		$customerCollection=$this->_customerCollectionFactory->create()->addFieldToFilter('email',$email);		
		return $customerCollection;
	}
}
