<?php
 
namespace Daytours\Bookingsystem\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\CatalogRule\Model\Rule as RuleCatalog;

class Data extends AbstractHelper
{

    //const PATTERN_PRICE_NORMAL = '/^[1-9]=\-*[0-9]([0-9])*$/';
    const PATTERN_PRICE_NORMAL = '/^([0-9]|[1-9][0-9])=([0-9]|[1-9][0-9])/';
    const PATTERN_RANGE_PRICE_NORMAL = '/^[1-9]([0-9])*\-[1-9]([0-9])*=\-*[0-9]([0-9])*$/';
    const PATTERN_RANGE_PRICE_PLUS = '/^[1-9]([0-9])*\+=\-*[0-9]([0-9])*$/';
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /** @var \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet **/
    protected $_attributeSet;

    protected $_transfer;

    protected $_rangePrice;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;
    /**
     * @var \Magebay\Bookingsystem\Helper\BkHelperDate
     */
    private $bkHelperDate;
    /**
     * @var RuleCatalog
     */
    private $ruleCatalog;

    /**
     * Data constructor.
     * @param Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet
     * @param \Daytours\Bookingsystem\Block\Transfer $transfer
     * @param Registry $coreRegistry
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magebay\Bookingsystem\Helper\BkHelperDate $bkHelperDate
     */
    public function __construct(
       Context $context,
       \Magento\Catalog\Model\ProductFactory $productFactory,
       \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet,
       \Daytours\Bookingsystem\Block\Transfer $transfer,
       Registry $coreRegistry,
       \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
       \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
       \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magebay\Bookingsystem\Helper\BkHelperDate $bkHelperDate,
       RuleCatalog $ruleCatalog
	)
	{
       parent::__construct($context);
        $this->_productFactory = $productFactory;
        $this->_attributeSet = $attributeSet;
        $this->_transfer = $transfer;
        $this->_coreRegistry = $coreRegistry;
        $this->_rangePrice = [];
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dateTime = $dateTime;
        $this->timezone = $timezone;
        $this->bkHelperDate = $bkHelperDate;
        $this->ruleCatalog = $ruleCatalog;
    }

    /**
     * If is transfer product
     * @param $bookingProductId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function ifProductIsTransfer($bookingProductId){
        $product = $this->_productFactory->create()->load($bookingProductId);
        $attributeSet = $this->_attributeSet->get($product->getAttributeSetId());
        $attributeSetName = $attributeSet->getAttributeSetName();
        if( $attributeSetName == $this->_transfer->getAttributeSetByTransfer() ){
            return true;
        }else{
            return false;
        }

    }

    /**
     * If current product is transfer
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function ifCurrentProductIsTransfer(){
        $product = $this->_coreRegistry->registry('product');
        $attributeSet = $this->_attributeSet->get($product->getAttributeSetId());
        $attributeSetName = $attributeSet->getAttributeSetName();
        if( $attributeSetName == $this->_transfer->getAttributeSetByTransfer() ){
            return true;
        }else{
            return false;
        }

    }
    /*
         * If is booking product
         * */
    public function isBookingProduct($bookingProductId){
        $product = $this->_productFactory->create()->load($bookingProductId);
        if( $product->getTypeId() == \Magebay\Bookingsystem\Model\Product\Type\Booking::TYPE_CODE ){
            return true;
        }else{
            return false;
        }

    }

    /**
     * transfer
     * @param $rangePrices
     */
    private function _evaluateRangePrice($rangePrices){
        foreach ($rangePrices as $item){
            if( preg_match(self::PATTERN_RANGE_PRICE_NORMAL,$item) || preg_match(self::PATTERN_PRICE_NORMAL,$item) ){
                $values = explode('=',$item);
                $priceAditional = $values[1];

                if( strpos($values[0],'-') !== false){
                    //range
                    $valuesRange = explode('-',$values[0]);
                    $this->_rangePrice['range'][] = [
                        'to' => $valuesRange[0],
                        'from' => $valuesRange[1],
                        'additional' => $priceAditional
                    ];
                }else{
                    //just number
                    $this->_rangePrice['range'][] = [
                        'to' => $values[0],
                        'from' => $values[0],
                        'additional' => $priceAditional
                    ];
                }


            }
            if( preg_match(self::PATTERN_RANGE_PRICE_PLUS,$item) ){
                $values = explode('=',$item);
                $priceAditional = $values[1];
                $to = intval($values[0]);
                $this->_rangePrice['plus'] = [
                    'to' => $to,
                    'additional' => $priceAditional
                ];
            }
        }
    }

    /**
     * @param $idProduct
     * @return array
     */
    public function _getRangePrice($idProduct){
        $product = $this->_productFactory->create()->load($idProduct);
        if($product){

            $typeTransferisRountrip = $this->_request->getParam('isRoundTrip');

            if( $typeTransferisRountrip ){
                $rangePrice = $product->getPrecioPorRango();
            }else{
                $rangePrice = $product->getPrecioPorRangoIda();
            }

            if($rangePrice && !empty(trim($rangePrice))){
                $splitRangePrice = preg_split('/(\r?\n)+/', $rangePrice);
                $this->_evaluateRangePrice($splitRangePrice);
                return $this->_rangePrice;
            }
        }
        return [];
    }

    /**
     * @param $idProduct
     * @param $qty
     * @param $price
     * @param bool $frontend
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPriceByRange($idProduct,$qty,$price,$frontend = true){

        $finalPrice = $price;
        if( $this->ifProductIsTransfer($idProduct) ){
            //transfer product

            if( $price > 0 ){
                $this->_getRangePrice($idProduct);
                if( isset($this->_rangePrice['range']) ){
                    foreach ($this->_rangePrice['range'] as $item){
                        if( $qty >= $item['to'] && $qty <= $item['from'] ){
                            if( $frontend ){
                                /*
                                 * Calcule from frontend JAVSCRIPT
                                 * Booking return price in format (price X qty)
                                */
                                $finalPrice += $item['additional'];
                            }else{
                                /*
                                 * Calcule from observer app/code/Daytours/RegularServices/Observer/Frontend/ChangePrice.php
                                 * Booking return return price by 1 unit
                                */
                                $finalPrice += $item['additional'];
                            }
                            return $finalPrice;
                        }
                    }
                }
                if( isset($this->_rangePrice['plus']) ){
                    $plus = $this->_rangePrice['plus'];
                    if( $qty >= $plus['to'] ){
                        if( $frontend ){
                            /*
                             * Calcule from frontend JAVSCRIPT
                             * Booking return price in format (price X qty)
                            */
                            $finalPrice += $plus['additional'];
                        }else{
                            /*
                             * Calcule from observer app/code/Daytours/RegularServices/Observer/Frontend/ChangePrice.php
                             * Booking return return price by 1 unit
                            */
                            $finalPrice += $plus['additional'];
                        }
                        return $finalPrice;
                    }
                }
            }

        }

        return $finalPrice;
    }

    /**
     * @param $checkin
     * @return string
     */
    private function validateDateBooking($checkin){
        $checkInFinal = $checkin;
        $formatDate = $this->bkHelperDate->getFieldSetting('bookingsystem/setting/format_date');
        if ($this->bkHelperDate->validateBkDate($checkin, $formatDate)) {
            $checkInFinal = $this->bkHelperDate->convertFormatDate($checkin);
        }
        return $checkInFinal;
    }

    /**
     * @param $checkin
     * @return bool
     */
    public function isToday($checkin){
        $checkinToCompare = $this->validateDateBooking($checkin);
        $currentDay = $this->timezone->date();
        $currentDayFormated = $currentDay->format('Y-m-d');

        if( $checkinToCompare == $currentDayFormated){
            return true;
        }
        return false;
    }

    /**
     * @param $checkin
     * @return bool
     */
    public function isTomorrow($checkin){
        $checkinToCompare = $this->validateDateBooking($checkin);
        $currentDay = $this->timezone->date();
        $currentDayFormated = $currentDay->format('Y-m-d');
        $dayMinusOne = new \DateTime($currentDayFormated);
        $dayMinusOne->modify('+1 day');
        $currentDayMinusOneFormated = $dayMinusOne->format('Y-m-d');

        if( $checkinToCompare == $currentDayMinusOneFormated){
            return true;
        }
        return false;
    }

    /**
     * @param $checkin
     * @return bool
     */
    public function verifyIfProductIsAvailableToBookLikeLastMinute($checkin){
        if( $this->isToday($checkin) || $this->isTomorrow($checkin) ){
            return true;
        }
        return false;
    }

    public function getPriceBetweenSpecialCalendarAndCatalogRule($product_id,$bookingPrice,$bookingSpecialPrice){
        $product = $this->_productFactory->create()->load($product_id);
        $priceWithRuleApplied = $this->ruleCatalog->calcProductPriceRule($product,$bookingPrice);
        if( $priceWithRuleApplied ){
            if( $bookingSpecialPrice > 0 ){
                return min($priceWithRuleApplied,$bookingSpecialPrice);
            }
            return $priceWithRuleApplied;
        }
        return $bookingSpecialPrice;
    }

    /**
     * @return false|string
     */
    public function getJsonLimitPeople(){
        $product = $this->_coreRegistry->registry('product');
        $arg = [
            'limit_going' => 0,
            'limit_rountrip' => 0
        ];

        if( $product->getLimitGoing() != '' && $product->getLimitGoing() !== null && is_numeric($product->getLimitGoing()) ){
            $arg['limit_going'] = abs($product->getLimitGoing());
        }
        if( $product->getLimitRountrip() != '' && $product->getLimitRountrip() !== null && is_numeric($product->getLimitRountrip()) ){
            $arg['limit_rountrip'] = abs($product->getLimitRountrip());
        }

        return json_encode($arg);
    }

}
 