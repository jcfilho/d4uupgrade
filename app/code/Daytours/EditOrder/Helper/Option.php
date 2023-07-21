<?php

namespace Daytours\EditOrder\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magebay\Bookingsystem\Helper\BkText;
use Magebay\Bookingsystem\Model\OptionsFactory;
use Magebay\Bookingsystem\Model\OptionsdropdownFactory;

class Option extends AbstractHelper
{
    /**
     * Allowed Option types
     */
    const ALLOWED_TYPES = array('field', 'area', 'file', 'drop_down', 'radio', 'checkbox', 'multiple');
    const CUSTOM_OPTION_EDITABLE = '_INTREQ';
    const CUSTOM_OPTION_CHECK_IN = 'CHECKIN';
    const BOOKING_OPTION_CHECK_IN = 'Check In';

    /**
     * @var BkText
     */
    protected $_bkText;

    /**
     * @var OptionsFactory
     */
    protected $_optionsFactory;

    /**
     * @var OptionsdropdownFactory
     */
    protected $_optionsdropdownFactory;

    /**
     * Option Helper constructor
     *
     * @param Context $context
     * @param BkText $bkText
     * @param OptionsFactory $optionsFactory
     * @param OptionsdropdownFactory $optionsdropdownFactory
     */
    public function __construct(
        Context $context,
        BkText $bkText,
        OptionsFactory $optionsFactory,
        OptionsdropdownFactory $optionsdropdownFactory
    )
    {
        parent::__construct($context);

        $this->_bkText = $bkText;
        $this->_optionsFactory = $optionsFactory;
        $this->_optionsdropdownFactory = $optionsdropdownFactory;
    }

    public function isCustomOptionEditable($option)
    {
        $sku = strtoupper($option->getSku());

        return $this->stringEndsWith($sku, self::CUSTOM_OPTION_EDITABLE);
    }

    public function isCustomOptionCheckIn($option)
    {
        $sku = strtoupper($option->getSku());

        return $this->stringEndsWith($sku, self::CUSTOM_OPTION_CHECK_IN);
    }

    public function isBookingCheckIn($option)
    {
        return $option['label'] == self::BOOKING_OPTION_CHECK_IN;
    }

    function stringEndsWith($haystack, $needle)
    {
        $length = strlen($needle);

        return $length === 0 ||
            (substr($haystack, -$length) === $needle);
    }

    /**
     * Prepare option for order item which will be created from this product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product\Option $option
     * @param string $value
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOrderOption($product, $option, $value)
    {
        $group = $option->groupFactory($option->getType())
            ->setOption($option)
            ->setProduct($product);

        return [
            'label' => $option->getTitle(),
            'value' => $group->getFormattedOptionValue($value),
            'print_value' => $group->getPrintableOptionValue($value),
            'option_id' => $option->getId(),
            'option_type' => $option->getType(),
            'option_value' => $value,
            'custom_view' => $group->isCustomizedView(),
        ];
    }

    /**
     * Prepare add-on for order item which will be created from this product
     *
     * @param array $addons
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOrderAddon($addons)
    {
        $bkTypeAddonPrice = __('Day');
        $additionalOptions = array();
        if (count($addons)) {
            $addonKeyExit = array();
            foreach ($addons as $key => $addon) {
                $typePriceAddon = '';
                $addonsModel = $this->_optionsFactory->create();
                $addonsOptions = $addonsModel->load($key);
                if ($addonsOptions->getOptionPriceType() == 1) {
                    $typePriceAddon = '/' . $bkTypeAddonPrice;
                }
                if ($addonsOptions->getId()) {
                    $titleAddons = $this->_bkText->showTranslateText($addonsOptions->getOptionTitle(), $addonsOptions->getOptionTitleTranslate());
                    if ($addonsOptions->getOptionType() == 1) {
                        $additionalOptions[] = array(
                            'label' => $titleAddons,
                            'value' => $addon,
                            'id' => $addonsOptions->getId()
                        );
                    } elseif ($addonsOptions->getOptionType() == 2 || $addonsOptions->getOptionType() == 4) {
                        $optionSelectModel = $this->_optionsdropdownFactory->create();
                        $valueRows = $optionSelectModel->getBkValueOptions($key);
                        $strTitleRow = '';
                        if (count($valueRows)) {
                            foreach ($valueRows as $valueRow) {
                                if ($valueRow->getId() == $addon) {
                                    $tempTitleRow = $this->_bkText->showTranslateText($valueRow->getDropdownTitle(), $valueRow->getDropdownTitleTranslate());
                                    /*if($valueRow->getDropdownPrice())
                                    {
                                        $tempTitleRow .= '( '. $this->_bkPiceHelper->currency($valueRow->getDropdownPrice(),true,false) . $typePriceAddon . ' ) ';
                                        $strTitleRow = $tempTitleRow;
                                    }*/
                                    $strTitleRow = $tempTitleRow;
                                    $additionalOptions[] = array(
                                        'label' => $titleAddons,
                                        'value' => $strTitleRow,
                                        'id' => $addonsOptions->getId()
                                    );
                                    break;
                                }
                            }
                        }
                    } elseif ($addonsOptions->getOptionType() == 3 || $addonsOptions->getOptionType() == 5) {
                        $optionSelectModel = $this->_optionsdropdownFactory->create();
                        $valueRows = $optionSelectModel->getBkValueOptions($key);
                        $strTitleRow = '';
                        if (count($valueRows)) {
                            foreach ($valueRows as $valueRow) {

                                if (count($addon)) {
                                    foreach ($addon as $mAddon) {
                                        if ($mAddon == $valueRow->getId()) {
                                            $tempTitleRow = $this->_bkText->showTranslateText($valueRow->getDropdownTitle(), $valueRow->getDropdownTitleTranslate());
                                            /*if($valueRow->getDropdownPrice() > 0)
                                            {
                                                $tempTitleRow .= '( '. $this->_bkPiceHelper->currency($valueRow->getDropdownPrice(),true,false) . $typePriceAddon . ' ) ';
                                            }*/
                                            if ($strTitleRow == '') {
                                                $strTitleRow .= $tempTitleRow;
                                            } else {
                                                $strTitleRow .= $tempTitleRow;
                                            }
                                        }
                                    }

                                }
                            }
                            if ($strTitleRow != '') {
                                $additionalOptions[] = array(
                                    'label' => $titleAddons,
                                    'value' => $strTitleRow,
                                    'id' => $addonsOptions->getId()
                                );
                            }
                        }
                    }
                }
            }
        }

        return $additionalOptions;
    }

    /**
     * Get option from product options config
     *
     * @param array $options
     * @param int $optionId
     * @return array
     */
    public function getOptionById($options, $optionId)
    {
        if (isset($options['options'])) {
            foreach ($options['options'] as $option) {
                if ($option['option_id'] == $optionId) {
                    return $option;
                }
            }
        }

        return null;
    }
}