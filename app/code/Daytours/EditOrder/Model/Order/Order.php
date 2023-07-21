<?php

namespace Daytours\EditOrder\Model\Order;

use Magebay\Bookingsystem\Helper\BkText;
use Magebay\Bookingsystem\Model\OptionsFactory;
use Daytours\EditOrder\Helper\Option;
use Daytours\EditOrder\Model\Order\Email\OrderSenderComplete as EmailOrder;
use Magento\Framework\Message\ManagerInterface as MessageManager;

class Order
{
    /**
     * @var BkText
     */
    protected $_bkText;

    /**
     * @var OptionsFactory
     */
    protected $_optionsFactory;

    /**
     * @var Option
     */
    protected $_optionHelper;
    /**
     * @var Email
     */
    private $emailOrderPostPurchase;
    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * Order options constructor
     *
     * @param BkText $bkText
     * @param OptionsFactory $optionsFactory
     * @param Option $optionHelper
     * @param EmailOrder $emailOrderPostPurchase
     * @param MessageManager $messageManager
     */
    public function __construct(
        BkText $bkText,
        OptionsFactory $optionsFactory,
        Option $optionHelper,
        EmailOrder $emailOrderPostPurchase,
        MessageManager $messageManager
    )
    {
        $this->_bkText = $bkText;
        $this->_optionsFactory = $optionsFactory;
        $this->_optionHelper = $optionHelper;
        $this->emailOrderPostPurchase = $emailOrderPostPurchase;
        $this->messageManager = $messageManager;
    }

    /**
     * Edit order's options
     *
     * @param \Magento\Sales\Model\Order $order
     * @param array $data
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function editOptions($order, $data)
    {
        if ($this->_anythingToProcess($data)) {
            foreach ($order->getItems() as $orderItem) {

                $product = $orderItem->getProduct();
                $options = $orderItem->getProductOptions();

                $form1Data = $this->_getExtraInfoForm1($orderItem, $product, $data);
                $form2Data = $this->_getExtraInfoForm2($orderItem, $product, $data);
                $form3Data = $this->_getExtraInfoForm3($orderItem, $product, $data);
                $form4Data = $this->_getExtraInfo($orderItem, $product, $data);

                $options = $this->_processProductOptions($orderItem, $product, $options, $form1Data, $form2Data, $form3Data, $form4Data);
                $orderItem->setProductOptions($options);
                $orderItem->save();
            }

            try {
                $order->setPostVenta(1);
                if ($order->save()) {
                    $this->emailOrderPostPurchase->send($order);
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(__('One error occurred, please contact with admin.'));
            }
        }
    }

    /**
     * Check if there is something to be processed
     *
     * @param array $data
     * @return boolean
     */
    protected function _anythingToProcess($data)
    {
        return (isset($data['form1']) && is_array($data['form1']))
            || (isset($data['form2']) && is_array($data['form2']))
            || (isset($data['form3']) && is_array($data['form3']))
            || (isset($data['adults']) && is_array($data['adults']));
    }

    /**
     * Get extra info for the product
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param \Magento\Catalog\Model\Product $product
     * @param array $data
     * @return array
     */
    protected function _getExtraInfoForm1($orderItem, $product, $data){
        $result = [];
        if( isset($data['form1']) ){
            if( isset($data['form1'][$orderItem->getId()]) ){
                if(isset($data['form1'][$orderItem->getId()][$product->getID()])  ){
                    $information = $data['form1'][$orderItem->getId()][$product->getID()];
                    $result[] = [
                        'label' => __('Hotel / Accommodation name'),
                        'value' => $information['hotel']
                    ];
                    $result[] = [
                        'label' => __('Address'),
                        'value' => $information['address']
                    ];
                }
            }

        }

        return $result;
    }

    /**
     * Get extra info for the product form2
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param \Magento\Catalog\Model\Product $product
     * @param array $data
     * @return array
     */
    protected function _getExtraInfoForm2($orderItem, $product, $data){
        $result = [];
        if( isset($data['form2']) ) {
            if( isset($data['form2'][$orderItem->getId()]) ){
                if(isset($data['form2'][$orderItem->getId()][$product->getID()])  ){
                    $information = $data['form2'][$orderItem->getId()][$product->getID()];
                    $result[] = [
                        'label' => __('Arrival flight details'),
                        'value' => $information['arrival_flight_details']
                    ];
                    $result[] = [
                        'label' => __('Arrival date'),
                        'value' => $information['arrival_date']
                    ];
                    $result[] = [
                        'label' => __('Est. Arrival time'),
                        'value' => $information['est_arrival_time_hour']." : ".$information['est_arrival_time_minute']
                    ];
                }
            }

        }
        return $result;
    }

    /**
     * Get extra info for the product form3
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param \Magento\Catalog\Model\Product $product
     * @param array $data
     * @return array
     */
    protected function _getExtraInfoForm3($orderItem, $product, $data){
        $result = [];
        if( isset($data['form3']) ) {
            if( isset($data['form3'][$orderItem->getId()]) ){
                if(isset($data['form3'][$orderItem->getId()][$product->getID()])  ){
                    $information = $data['form3'][$orderItem->getId()][$product->getID()];
                    $result[] = [
                        'label' => __('Departing flight details'),
                        'value' => $information['departing_flight_details']
                    ];
                    $result[] = [
                        'label' => __('Departing date'),
                        'value' => $information['departing_date']
                    ];
                    $result[] = [
                        'label' => __('Departure time'),
                        'value' => $information['departure_time_hour']." : ".$information['departure_time_minute']
                    ];
                }
            }

        }
        return $result;
    }

    /**
     * Get extra info for the product
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param \Magento\Catalog\Model\Product $product
     * @param array $data
     * @return array
     */
    protected function _getExtraInfo($item, $product, $data)
    {
        $extraInfo = [];

            $adultChild = array('adults' => 'Adult', 'children' => 'Child');
            if (
                $product->getExtraInfo()
                && is_array($data)
            ) {
                foreach ($adultChild as $k => $v) {
                    if (
                        isset($data[$k])
                        && isset($data[$k][$item->getId()][$product->getId()])
                    ) {
                        $info = $data[$k][$item->getId()][$product->getId()];
                        $values = [
                            'name' => __('Full name'),
                            'passport_number' => __('Passport number'),
                            'date_of_birth' => __('Date of Birth'),
                            'nationality' => __('Nationality'),
                            'gender' => __('Gender')
                        ];
                        for ($i = 1; $i <= count($info); $i++) {
                            foreach ($values as $k1 => $v1) {
                                $extraInfo[] = [
                                    'label' => "$v1 (" . __(sprintf("$v %s", $i)) . ')',
                                    'value' => $info[$i][$k1]
                                ];
                            }
                        }
                    }
                }
            }
        return $extraInfo;
    }

    /**
     * Process options and add-ons to be saved in the order
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param \Magento\Catalog\Model\Product $product
     * @param array $options
     * @param array $optionsConfig
     * @param array $addons
     * @param array $extraInfo
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _processProductOptions($item, $product, $options, $form1Data, $form2Data, $form3Data, $form4Data)
    {
        if (!empty($form1Data)) {
            $options = $this->_saveExtraInfo($options, $form1Data);
        }
        if (!empty($form2Data)) {
            $options = $this->_saveExtraInfo($options, $form2Data);
        }
        if (!empty($form3Data)) {
            $options = $this->_saveExtraInfo($options, $form3Data);
        }
        if (!empty($form4Data)) {
            $options = $this->_saveExtraInfo($options, $form4Data);
        }

        return $options;
    }

    /**
     * Process options to be saved in the order
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $options
     * @param array $optionsConfig
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _processOptions($product, $options, $optionsConfig)
    {
        if ($product->getTypeInstance()->hasOptions($product)) {
            foreach ($optionsConfig as $optionId => $value) {
                $optionModel = $product->getOptionById($optionId);
                if ($optionModel) {
                    $options = $this->_saveOptionInOptions($product, $options, $optionModel, $value);
                    $options = $this->_saveOptionInBuyRequest($options, $optionModel, $value);
                }
            }
        }

        return $options;
    }

    /**
     * Save option in order "options" array
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $options
     * @param \Magento\Catalog\Model\Product\Option $optionModel
     * @param int | string | array $value
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _saveOptionInOptions($product, $options, $optionModel, $value)
    {
        if (in_array($optionModel->getType(), $this->_optionHelper::ALLOWED_TYPES)) {
            $newOptions = [];
            if (isset($options['options'])) {
                $newOption = true;
                foreach ($options['options'] as $i => $option) {
                    if ($option['option_id'] == $optionModel->getId()) {
                        // edit option
                        $options['options'][$i] = array_merge(
                            $option,
                            $this->_optionHelper->getOrderOption($product, $optionModel, implode(',', (array)$value))
                        );
                        $newOption = false;
                    }
                }
                if ($newOption) {
                    // add option
                    $newOptions[] = $this->_optionHelper->getOrderOption($product, $optionModel, implode(',', (array)$value));
                }
            } else {
                // add option
                $newOptions[] = $this->_optionHelper->getOrderOption($product, $optionModel, implode(',', (array)$value));
            }
            $options['options'] = isset($options['options']) ? $options['options'] : array();
            $options['options'] = array_merge($options['options'], $newOptions);
        }

        return $options;
    }

    /**
     * Save option in order "info_buyRequest" array
     *
     * @param array $options
     * @param \Magento\Catalog\Model\Product\Option $optionModel
     * @param int | string | array $value
     * @return array
     */
    protected function _saveOptionInBuyRequest($options, $optionModel, $value)
    {
        if (in_array($optionModel->getType(), $this->_optionHelper::ALLOWED_TYPES)) {
            if (isset($options['info_buyRequest']['options'])) {
                $options['info_buyRequest']['options'][$optionModel->getId()] = $value;
            } else {
                // add option
                $options['info_buyRequest']['options'] = [$optionModel->getId() => $value];
            }
        }

        return $options;
    }

    /**
     * Save addon in order "additional_options" array
     *
     * @param array $options
     * @param array $addons
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _saveAddonInAddons($options, $addons)
    {
        foreach ($addons as $addonId => $value) {
            $addonsModel = $this->_optionsFactory->create();
            $addonsOptions = $addonsModel->load($addonId);
            if ($addonsOptions->getId()) {
                foreach ($options['additional_options'] as $i => $option) {
                    $titleAddons = $this->_bkText->showTranslateText($addonsOptions->getOptionTitle(), $addonsOptions->getOptionTitleTranslate());
                    if ($option['label'] == $titleAddons) {
                        $newValues = $this->_optionHelper->getOrderAddon($addons);
                        foreach ($newValues as $newValue) {
                            if ($newValue['id'] == $addonsOptions->getId()) {
                                $options['additional_options'][$i]['value'] = $newValue['value'];
                            }
                        }
                        // TODO: update also info_buyRequest
                        //$options = $this->_processBuyRequest($options, $addonId, $value);
                    }
                }
            }
        }

        return $options;
    }

    /**
     * Save extra info in order "additional_options" array
     *
     * @param array $options
     * @param array $extraInfo
     * @return array
     */
    protected function _saveExtraInfo($options, $extraInfo)
    {
        if (isset($options['additional_options'])) {
            foreach ($extraInfo as $info) {
                $options['additional_options'][] = $info;
            }
        } else {
            $options['additional_options'] = $extraInfo;
        }

        return $options;
    }
}