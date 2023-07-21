<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Daytours\ConsoleUtils\Console\Command;

//use Daytours\Bookingsystem\Model\Calendars;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magebay\Bookingsystem\Model\CalendarsFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Daytours\EditOrder\Model\Order\Email\LogFactory;


/**
 * Class generic
 */
class Generic extends Command
{
    /** @var \Magento\Framework\App\State **/
    private $state;

    /** @var  \Magento\Store\Model\StoreManagerInterface **/
    private $_storeManager;

    private $calendarsFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     **/
    protected $_timezone;

    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * @var \Daytours\EditOrder\Helper\OrderSender
     */
    protected $orderSender;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

	protected $_options;


    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CalendarsFactory $calendarsFactory,
        TimezoneInterface $timezone,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        LogFactory $logFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Catalog\Model\Product\Option $options
        //\Daytours\EditOrder\Helper\OrderSender $orderSender
    ) {
        $this->state = $state;
//        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND); //NO QUITAR!!
        //$this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML); //NO QUITAR!!
        //$this->state->setAreaCode('adminhtml');
        //adminhtml
        $this->_storeManager = $storeManager;
        $this->calendarsFactory = $calendarsFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_timezone = $timezone;
        $this->logFactory = $logFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
		$this->_options = $options;

        //$this->orderSender = $orderSender;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('utils:generic')
            ->setDescription('Ejecutar un comando genérico, de propósito general.');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND); //NO QUITAR!!
        //var_dump($collection->toArray());

        // foreach ($categories as $category) {
        //     $cantidadDeEspacios = 40-strlen($category->getName());
        //     $espacios = "";
        //     for($i=0;$i<$cantidadDeEspacios;$i++){
        //         $espacios .= " ";
        //     }
        //     $output->writeln($category->getName().$espacios." | ".$category->getUrl());
        // }

        //ORDER -----------------------------
        // $orders = $this->getOrdersCollection();
        // $ordersArray = $orders->toArray();
        // $lastOrder = end($ordersArray);
        // foreach($ordersArray["items"] as $item){
        //     if($item["entity_id"] == "694"){
        //         echo json_encode($item);
        //     }
        // }
        // return;

        //PRODUCTS ------------------------------
        $products = $this->getProductCollection()->getItems();
        foreach ($products as $product) {
            $calendarBookingId = $product->getId();
            $model = $this->calendarsFactory->create();
            $collection = $model->getBkCalendars();
            $calendarCollection = $collection->addFieldToFilter('calendar_booking_id',$calendarBookingId);
            foreach($calendarCollection as $calendar){
                $calendar->setData("calendar_price",$product->getPrice());
                $calendar->setData("calendar_promo",$product->getSpecialPrice());
                $calendar->save();
            }
            echo $product->getName()." - ID: ".$product->getId()." - PRICE: ".$product->getPrice()."  - OK\n";
        }
        // foreach ($products as $product) {
   
        // }
        // $collection = $this->productCollectionFactory->create();
        //$product = end($products);
        //echo $product->getName() . "\n";

        //echo "TEST PRODUCT OPTION ADITION\n";

        //$this->addPartialPaymentOption($product);

        //echo "OPTIONS:\n" ---------------------------------------------------------------------;
        //$options = $product->getOptions();
        // $options = $product->getProductOptionsCollection()->toArray();

        // if($options == null){
        //     echo "options es NULL";
        //     return;
        // }
        // foreach($options as $op){
        //     var_dump($op);
        //     //echo "--" . $op->getTitle() . "\n";
        // }

        //GET VALUES SELECTED OF SPECIFIC PRODUCT ATRIBUTE
        //$attributes = $product->getAttributes();
        //$attr = $product->getAttributeText('partial_payment');
        //echo "--" . $attr .": \n";

        // GET PRODUCT ATRIBUTES WITH OPTIONS VALUES ---------------------
        // foreach ($attributes as $a) {
        //     if($a->getName() == "partial_payment"){
        //         echo "--" . $a->getName() .": \n";//partial_payment
        //         $options = $a->getOptions();
        //         foreach($options as $op){
        //             echo "------" . $op->getLabel() . ": " . $op->getValue() . "\n";
        //         }
        //     }
        // }

        // $order = $this->getSpecificOrder(693);
        //echo "Order Id: " . 693 . "\n";
        //echo "Get Remain Days: " . $this->getCountdown($order) . "\n";
        //echo "CheckFrecuency: " . strval($this->checkFrequency($order, $this->getCountdown($order))) . "\n";
        //$this->orderSender->sendMissingOrderDataEmails();
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
				if(strtolower($op->getTitle()) == "paid partially"){
					$optionExist = true;
				}
			}

			if(!$optionExist){
                //echo "OPTION EXISTE \n";
				//$optionInstance = $product->getOptionInstance();
				//CREATE CUSTOM OPTION
				$customOption = $objectManager->create('Magento\Catalog\Api\Data\ProductCustomOptionInterface');
				$customOption->setTitle('Paid Partially')
					->setType('checkbox')
					->setIsRequire(true)
					->setSortOrder(1)
					->setPrice(-80.00)
					->setPriceType('percent')
					->setMaxCharacters(50)
					->setProductSku($product->getSku());
		
				//CREATE OPTION VALUE
				$optionValue = $objectManager->create('Magento\Catalog\Model\Product\Option\Value');
				$optionValue->setSortOrder(0);
				$optionValue->setTitle("20%");
				$optionValue->setPrice(-80.00);
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
				if(strtolower($op->getTitle()) == "paid partially"){
					$op->delete();
					return;
				}
			}
		}
	}

    function getCategoriesCollection()
    {
        $objectManager = $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        $categories = $categoryFactory->create()
            ->addAttributeToSelect('*')
            ->setStore($this->_storeManager->getStore())
            ->addAttributeToFilter('is_active', '1');
        return $categories;
    }

    function getCalendarCollection()
    {
        $model = $this->calendarsFactory->create();
        return $model->getBkCalendars();
    }

    public function getOrdersCollection()
    {
        return $this->_orderCollectionFactory->create()->addAttributeToSelect('*');
    }

    public function getSpecificOrder($orderId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($orderId);
        return $order;
    }

    public function getProductCollection()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        return $collection;
    }

    /**
     * Get remaining days
     *
     * @param \Magento\Catalog\Model\Order $order
     * @return int
     */
    public function getCountdown($order)
    {
        $checkIns = array();
        foreach ($order->getItems() as $orderItem) {
            if ($orderItem->getProductOptions()) {
                if (isset($orderItem->getProductOptions()['info_buyRequest'])) {
                    if (isset($orderItem->getProductOptions()['info_buyRequest']['temp_check_in'])) {
                        $checkin = $orderItem->getProductOptions()['info_buyRequest']['temp_check_in'];
                        $checkIns[] = date('Y-m-d', strtotime($checkin));
                        echo "Temp Check In: " . $checkin . "\n";
                    }
                }
            }
        }
        usort($checkIns, function ($a, $b) {
            return strtotime($a) - strtotime($b);
        });
        if (count($checkIns)) {
            $storeDate = $this->_timezone->date();
            $start = new \DateTime($storeDate->format('Y-m-d'));
            $end = new \DateTime($checkIns[0]);
            $diff = $start->diff($end);
            echo "End: " . $checkIns[0] . "\n";

            return $diff->days;
        }

        return -1;
    }

    /**
     * Check frequency for sending email
     *
     * @param \Magento\Catalog\Model\Order $order
     * @param int $countdown
     * @return bool
     */
    public function checkFrequency($order, $countdown)
    {
        $collection = $this->logFactory->create()->getCollection();
        $collection->addFieldToFilter('order_id', ["eq" => $order->getId()]);

        if ($collection->count() == 0) {
            return true;
        }
        echo "FLAG 1";
        foreach ($collection as $log) {
            $start = new \DateTime($log->getSentAt());
            $storeDate = $this->_timezone->date();
            $end = new \DateTime($storeDate->format('Y-m-d H:i:s'));
            $diff = $start->diff($end);

            //var_dump($diff);
            // var_dump(($diff->days * 24) + $diff->h);
            // if ($countdown < 7) {
            //     $hours = ($diff->days * 24) + $diff->h;
            //     if ($hours >= 12) {
            //         return true;
            //     }
            // } else if ($countdown < 30) {
            //     if ($diff->days >= 1) {
            //         return true;
            //     }
            // } else {
            //     if ($diff->days >= 7) {
            //         return true;
            //     }
            // }

            // if ($diff->days >= 3) {
            //     return true;
            // }
            if ($diff->i >= 5) {
                return false;
            }
        }
        return false;
    }
}
