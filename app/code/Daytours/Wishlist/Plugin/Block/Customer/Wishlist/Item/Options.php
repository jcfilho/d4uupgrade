<?php

namespace Daytours\Wishlist\Plugin\Block\Customer\Wishlist\Item;

class Options
{

    /**
     * @var \Daytours\Bookingsystem\Helper\Data
     */
    protected $_bookingHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magebay\Bookingsystem\Model\ResourceModel\Options\CollectionFactory
     */
    protected $_bookingOptionsCollection;

    /**
     * @var \Magento\Wishlist\Model\Item\OptionFactory
     */
    protected $_wislistItemOptionCollectionFactory;

    /**
     * @var \Magebay\Bookingsystem\Model\OptionsdropdownFactory
     */
    protected $_bookingOptionDropdown;

    /**
     * @var \Magebay\Bookingsystem\Model\OptionsdropdownFactory
     */
    protected $_bookingIntervalHours;
    /**
     * @var \Magebay\Bookingsystem\Helper\Data
     */
    private $bookingData;

    /**
     * Options constructor.
     * @param \Daytours\Bookingsystem\Helper\Data $bookingHelper
     * @param \Magento\Wishlist\Model\ResourceModel\Item\Option\CollectionFactory $wislistItemOptionCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magebay\Bookingsystem\Model\ResourceModel\Options\CollectionFactory $bookingOptionsCollection
     * @param \Magebay\Bookingsystem\Model\OptionsdropdownFactory $bookingOptionDropdown
     */
    public function __construct(
        \Daytours\Bookingsystem\Helper\Data $bookingHelper,
        \Magento\Wishlist\Model\ResourceModel\Item\Option\CollectionFactory $wislistItemOptionCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magebay\Bookingsystem\Model\ResourceModel\Options\CollectionFactory $bookingOptionsCollection,
        \Magebay\Bookingsystem\Model\OptionsdropdownFactory $bookingOptionDropdown,
        \Magebay\Bookingsystem\Model\IntervalhoursFactory $bookingIntervalHours,
        \Magebay\Bookingsystem\Helper\Data $bookingData
    )
    {
        $this->_bookingHelper = $bookingHelper;
        $this->_wislistItemOptionCollectionFactory = $wislistItemOptionCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_bookingOptionsCollection = $bookingOptionsCollection;
        $this->_bookingOptionDropdown = $bookingOptionDropdown;
        $this->_bookingIntervalHours = $bookingIntervalHours;
        $this->bookingData = $bookingData;
    }

    public function afterGetConfiguredOptions(\Magento\Wishlist\Block\Customer\Wishlist\Item\Options $subject, $result)
    {

        $dataItemWishlist = $subject->getItem()->getData();
        $product = $dataItemWishlist['product'];
        $productTypeId = $product->getTypeId();

        if( $productTypeId == \Magebay\Bookingsystem\Model\Product\Type\Booking::TYPE_CODE ){

            $storeId = $this->_storeManager->getStore()->getId();
            $productId = $dataItemWishlist['product_id'];
            $wishlistItemId = $dataItemWishlist['wishlist_item_id'];

            $collectionOptions = $this->_wislistItemOptionCollectionFactory->create();
            $collectionOptions->addFieldToFilter('wishlist_item_id',$wishlistItemId)
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('code',[ 'neq' => 'info_buyRequest' ])
                ->addFieldToFilter('code',['like' => '%optionsbooking%']);

            $isTransfer = $this->_bookingHelper->ifProductIsTransfer($productId);

            foreach ($collectionOptions as $key => $itemOption){

                $code = $itemOption->getCode();
                $codeSplit = explode('_',$code);
                if( is_numeric($codeSplit[1]) || $codeSplit[1] == 'interval' ){
                    /*
                     * is_numeric($codeSplit[1])        => It's an Addons
                     * $codeSplit[1] == 'interval'      => It's a interval option
                     * */
                    $option['option_id']    = $itemOption->getCode();
                    $option['custom_view']  = false;
                    if($codeSplit[1] == 'interval'){
                        $valueLabelType         = $this->getDataOptionBookingInterval($productId,$itemOption->getValue(),$storeId);
                    }else{
                        $idOptionBooking = $codeSplit[1];
                        $valueLabelType         = $this->getDataOptionBooking($idOptionBooking,$productId,$itemOption->getValue(),$storeId);
                    }



                    $options_merged         = array_merge($option,$valueLabelType);
                    $result[]               = $options_merged;

                }else{
                    if( $isTransfer && ($itemOption->getCode() == 'optionsbooking_check_out' || $itemOption->getCode() == 'optionsbooking_check_out_two') ){

                    }else{

//                        if( $itemOption->getCode() == 'optionsbooking_check_out' && $this->ifIsPerTime($collectionOptions->getData()) ){
//
//                        }else{
                            $option = [];
                            $option['label']        = $this->getLabelBooking($itemOption->getCode(),$isTransfer);
                            $option['option_id']    = $itemOption->getCode();
                            $option['option_type']  = $this->getTypeBooking($itemOption->getCode(),$isTransfer);
                            $option['custom_view']  = false;
                            $values                 = $this->getValueBookingCustom($itemOption->getCode(),$itemOption->getValue(),$isTransfer);

                            $options_merged         = array_merge($option,$values);
                            $result[]               = $options_merged;
                        }

//                    }
                }

            }

        }

        return $result ;
    }

    private function ifIsPerTime($options){
        $result = false;
        foreach ($options as $item){
            if($item->getCode() == 'optionsbooking_service_start' || $item->getCode() == 'optionsbooking_service_end'){
                $result = true;
            }
            break;
        }
        return $result;
    }

    private function getLabelBooking($code,$transfer){
        $value = '';
        switch ($code){
            case 'optionsbooking_check_in':
                $value = ($transfer) ? __('Date of arrival')->getText() :  __('Date')->getText();
                break;
            case 'optionsbooking_check_out':
                $value = ($transfer) ?  __('Date of arrival')->getText() :  __('Check Out')->getText();
                break;
            case 'optionsbooking_check_in_two':
                $value = ($transfer) ?  __('Date of departure')->getText() :  __('Date')->getText();
                break;
            case 'optionsbooking_check_out_two':
                $value = ($transfer) ?  __('Date of departure')->getText() :  __('Check Out')->getText();
                break;
            case 'optionsbooking_goingroundtrip':
                $value =  __('Type transfer')->getText();
                break;
            case 'optionsbooking_service_start':
                $value =  __('Service Start')->getText();
                break;
            case 'optionsbooking_service_end':
                $value =  __('Service End')->getText();
                break;
        }
        return $value;
    }

    private function getTypeBooking($code,$transfer){
        $value = '';
        switch ($code){
            case 'optionsbooking_check_in':
                $value = 'date';
                break;
            case 'optionsbooking_check_out':
                $value = 'date';
                break;
            case 'optionsbooking_check_in_two':
                $value = 'date';
                break;
            case 'optionsbooking_check_out_two':
                $value = 'date';
                break;
            case 'optionsbooking_goingroundtrip':
                $value = 'radio';
                break;
            case 'optionsbooking_service_start':
                $value = 'drop_down';
                break;
            case 'optionsbooking_service_end':
                $value = 'drop_down';
                break;
        }
        return $value;
    }

    private function getValueBookingCustom($code,$value,$isTransfer){
        $option = [];
        if( $code == 'optionsbooking_goingroundtrip' ){
            $option['value']        =  ($value == 1) ? __('Going')->getText() : __('Rountrip')->getText();
            $option['print_value']  =  ($value == 1) ? __('Going')->getText() : __('Rountrip')->getText();

        }

        if( $code == 'optionsbooking_check_in' ){
            $option['value']        = $value;
            $option['print_value']  = $value;

        }

        if( $code == 'optionsbooking_check_out' ){
            $option['value']        = $value;
            $option['print_value']  = $value;

        }

        if( $code == 'optionsbooking_service_start' ){
            $option['value']        = $this->getTimeHourService($value);
            $option['print_value']  = $this->getTimeHourService($value);

        }
        if( $code == 'optionsbooking_service_end' ){
            $option['value']        = $this->getTimeHourService($value);
            $option['print_value']  = $this->getTimeHourService($value);

        }

        if( $isTransfer ){
            if( $code == 'optionsbooking_check_in_two' ){
                $option['value']        = $value;
                $option['print_value']  = $value;

            }
        }
        if( $isTransfer ){
            if( $code == 'optionsbooking_check_out_two' ){
                $option['value']        = $value;
                $option['print_value']  = $value;

            }
        }
        return $option;
    }

    private function getDataOptionBooking($optionId,$productId,$valueId,$storeId){

        $result = [];

        $collection = $this->_bookingOptionsCollection->create();
        $collection->addFieldToSelect(['option_id','option_type','option_booking_id','option_title_translate'])
                    ->addFieldToFilter('option_id', $optionId)
                    ->addFieldToFilter('option_booking_id', $productId);

        if($collection->getSize()){
            $dataOption = $collection->getFirstItem();
            $result['option_type'] = $this->getTypeOptionBooking($dataOption->getOptionType());

            $titlesByStore = json_decode($dataOption->getOptionTitleTranslate());
            foreach ($titlesByStore as $item){
                if( $item->store_id == $storeId ){
                    $result['label'] = $item->value;
                    break;
                }
            }

            if( $dataOption->getOptionType() != 1 ){
                if( $result['option_type'] == 'checkbox' || $result['option_type'] == 'multi_drop_down' ){
                    /*Separe IDs by coma*/
                    $resultvalues = $this->_getResultFromSeveralIntems($valueId,$storeId,false);
                    $result = array_merge($result,$resultvalues);
                }else{
                    $resultvalues = $this->_getResultFromIdOption($valueId,$storeId);
                    $result = array_merge($result,$resultvalues);
                }

            }else{
                $result['value'] = $valueId;
                $result['print_value'] = $valueId;
            }

        }
        return $result;
    }

    private function getDataOptionBookingInterval($productId,$valueId,$storeId){

        $result = [];

        $result['option_type'] = $this->getTypeOptionBooking(6);
        $result['label'] = 'Interval';

        /*Separe IDs by coma*/
        $resultvalues = $this->_getResultFromSeveralIntems($valueId,$storeId,true);
        $result = array_merge($result,$resultvalues);

        return $result;
    }

    private function _getResultFromSeveralIntems($valueId,$storeId,$isInterval = false){
        $valueIdArray = explode(',',$valueId);
        $result = [];
        foreach ($valueIdArray as $keyItemIdOption => $itemIdOption){
            if( $isInterval ){
                $valueOption = $this->_bookingIntervalHours->create()->load($itemIdOption);
                $intervalHour = $valueOption->getIntervalhoursBookingTime();
                $result['value'][] = $this->_getHourFormat($intervalHour);
                $result['print_value'][] = $this->_getHourFormat($intervalHour);

            }else{
                $valueOption = $this->_bookingOptionDropdown->create()->load($itemIdOption);
                $titlesDropdownOptionByStore = json_decode($valueOption->getDropdownTitleTranslate());
                /*Get title by store*/
                foreach ($titlesDropdownOptionByStore as $itemdd){
                    if( $itemdd->store_id == $storeId ){
                        $result['value'][] = $itemdd->value;
                        $result['print_value'][] = $itemdd->value;
                        break;
                    }
                }
            }
        }

        if( $isInterval ){
            $result['value'] = implode("<br/> " , $result['value']);
            $result['print_value'] = implode("<br/> " , $result['print_value']);
        }else{
            $result['value'] = implode(", " , $result['value']);
            $result['print_value'] = implode(", " , $result['print_value']);
        }

        //sort($result);
        return $result;
    }

    private function _getResultFromIdOption($valueId,$storeId){
        $result = [];
        $valueOption = $this->_bookingOptionDropdown->create()->load($valueId);
        $titlesDropdownOptionByStore = json_decode($valueOption->getDropdownTitleTranslate());
        /*Get title by store*/
        foreach ($titlesDropdownOptionByStore as $itemdd){
            if( $itemdd->store_id == $storeId ){
                $result['value'] = $itemdd->value;
                $result['print_value'] = $itemdd->value;
                break;
            }
        }

        return $result;
    }

    private function getTypeOptionBooking($typeId){
        $result = '';
        switch ($typeId)
        {
            case 1 :
                $result = 'Field';
                break;
            case 2 :
                $result = 'drop_down';
                break;
            case 3 :
                $result = 'multi_drop_down';
                break;
            case 4 :
                $result = 'radio';
                break;
            case 5 :
                $result = 'checkbox';
                break;
            case 6 :
                $result = 'interval';
                break;
        }
        return $result;
    }

    private function _getHourFormat($time){
        $arIntervals = explode('_',$time);
        $textType1 = __('AM');
        $textType2 = __('AM');
        $tempIntHoursStart = strtotime("{$arIntervals[0]}:{$arIntervals[1]}:00");

        if($arIntervals[0] >= 12)
        {
            $arIntervals[0] = $arIntervals[0] > 12 ? $arIntervals[0] - 12 : $arIntervals[0];
            $textType1 = __('PM');
        }
        if($arIntervals[2] >= 12)
        {
            $arIntervals[2] = $arIntervals[2] > 12 ? $arIntervals[2] - 12 : $arIntervals[2];
            $textType2 = __('PM');
        }

        $result = $arIntervals[0] . ':' . $arIntervals[1] . ' ' . $textType1 . ' ' . $arIntervals[2] . ':' . $arIntervals[3] . ' ' . $textType2;

        return $result;
    }

    private function getTimeHourService($time){
        $intTime = strtotime($time);
        $h = date('H',$intTime);
        $m = date('i',$intTime);
        $timeModel = $this->bookingData->getFieldSetting('bookingsystem/setting/time_mode');
        $textTime = $h.':'.$m;
        if($timeModel == 1)
        {
            $typeTime = $h >= 12 ? __('PM') : __('AM');
            $h = $h > 12 ? $h - 12 : (int)$h;
            $h = $h < 10 ? '0'.$h : $h;
            $textTime = $h.':'.$m.' '.$typeTime;
        }
        return $textTime;
    }
}