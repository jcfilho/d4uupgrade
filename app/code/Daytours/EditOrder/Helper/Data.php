<?php
/**
 * Created by PhpStorm.
 * User: filho
 * Date: 24/01/19
 * Time: 8:53 AM
 */

namespace Daytours\EditOrder\Helper;

use Magento\Framework\App\Helper\Context;
use \Magento\Sales\Model\Order as ModelOrder;
use \Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    /**
     * @var ModelOrder
     */
    private $orderModel;
    /**
     * @var Session
     */
    private $session;
    /**
     * @var Option
     */
    private $customOption;
    /**
     * @var EncryptorInterface
     */
    private $encryptor;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Context $context,
        ModelOrder $orderModel,
        Session $session,
        Option $customOption,
        EncryptorInterface $encryptor,
        Registry $registry,
        StoreManagerInterface $storeManager
    )
    {

        parent::__construct($context);
        $this->orderModel = $orderModel;
        $this->session = $session;
        $this->customOption = $customOption;
        $this->encryptor = $encryptor;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
    }

    public function isExistInCurrentOrder($optiosOrder, $optionId){
        foreach ($optiosOrder as $option){
            if($optionId == $option['option_id'] ){
                return true;
            }
        }
        return false;
    }

    public function verifyIfProductHasPostorderOptions($product){
        if ($product) {
            if( $product->getExtraInfo() ){
                if( $product->getPostCompraForm1() || $product->getPostCompraForm2() || $product->getPostCompraForm3() || $product->getPostCompraForm4() ){
                    return true;
                }
            }
        }
        return false;
    }

    public function ifMissingFieldsToPostOrderCurrentProduct(){
        $product = $this->registry->registry('product');
        return $this->verifyIfProductHasPostorderOptions($product);
    }

    /**
     * Get remaining days
     *
     * @param \Magento\Catalog\Model\Order $order
     * @return int
     */
    public function ifMissingFieldsToPostOrder($order)
    {
        $result = false;
        $orderItems = $order->getAllItems();
        foreach ($orderItems as $item) {
            $product = $item->getProduct();
            $result = $this->verifyIfProductHasPostorderOptions($product);
            if( $result ){
                return true;
            }
        }
        return $result;
    }

    public function getURLEncrypted($orderId, $customerEmail,$storeId){
        $data = $this->encryptor->encrypt($orderId.'|'.$customerEmail);
        return $this->storeManager->getStore($storeId)->getUrl('editorder/order/edit/data/' . base64_encode($data));
    }

    public function getDataURLDecrypted($data){
        $data = $this->encryptor->decrypt(base64_decode($data));
        $data = explode('|',$data);
        return $data;
    }

    /**
     * Get remaining days
     *
     * @param Integer $order_id
     * @return int
     */
    public function isVisibleCompleteOrderButton($order_id){
        $orderById = $this->orderModel->loadByIncrementId($order_id);
        return $this->ifMissingFieldsToPostOrder($orderById);
    }
}