<?php

namespace Daytours\PartialPayment\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductOptions implements ObserverInterface
{
	protected $_options;

	public function __construct(
		\Magento\Catalog\Model\Product\Option $options
	) {
		$this->_options = $options;
	}

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$product = $observer->getProduct();
		$this->addPartialPaymentOption($product);
	}

	public function addPartialPaymentOption($product)
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $enablePartialPayment = $product->getAttributeText('partial_payment');
        //echo "ENABLE PARTIALPEYMENT: " . $enablePartialPayment ."\n";

		if($enablePartialPayment && strtolower($enablePartialPayment) == "yes"){
            ///echo "PARTIAL PAYMENT ESTA HABILITADO\n";
			$options = $product->getProductOptionsCollection()->getItems();
			$optionExist = false;
			foreach($options as $op){
				if(strtolower($op->getTitle()) == "pay partially"){
					$optionExist = true;
				}
			}

			if(!$optionExist){
                //echo "OPTION EXISTE \n";
				//$optionInstance = $product->getOptionInstance();
				//CREATE CUSTOM OPTION
				$customOption = $objectManager->create('Magento\Catalog\Api\Data\ProductCustomOptionInterface');
				$customOption->setTitle('Pay Partially')
					->setType('checkbox')
					->setIsRequire(false)
					->setSortOrder(1)
					->setPrice(0.00)
					->setPriceType('percent')
					->setMaxCharacters(50)
					->setProductSku($product->getSku());
		
				//CREATE OPTION VALUE
				$optionValue = $objectManager->create('Magento\Catalog\Model\Product\Option\Value');
				$optionValue->setSortOrder(0);
				$optionValue->setTitle("20%");
				$optionValue->setPrice(0.00);
				$optionValue->setPriceType("percent");
				$optionValue->setSku($product->getSku());
				$customOption->addValue($optionValue);
		
				//ADD CUSTOM OPTION TO PRODUCT
				//$product->addOption($customOption);
				$product->addOption($customOption);
                $product->setHasOptions(true);
                $product->save();
                //echo "SE HAN GUARDADO LOS CAMBIOS \n";
			}
            else{
                //echo "YA EXISTE LA OPTION \n";
            }
		}
		else{
            //echo "ELIMINADO CUSTOM OPTION \n";
            
			//REMOVE CUSTOM OPTION
			$options = $product->getProductOptionsCollection()->getItems();
			foreach($options as $op){
				if(strtolower($op->getTitle()) == "pay partially"){
					$op->delete();
					return;
				}
			}
		}
	}
}
