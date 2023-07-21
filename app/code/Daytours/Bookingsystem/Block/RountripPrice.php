<?php
/**
 * Created by PhpStorm.
 * User: jose
 * Date: 10/05/18
 * Time: 5:10 PM
 */

namespace Daytours\Bookingsystem\Block;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\Registry;
use \Daytours\Bookingsystem\Helper\Data as DataBookingSystem;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

class RountripPrice extends Template
{
    /**
     * @var DataBookingSystem
     */
    private $dataBookingSystem;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var PriceHelper
     */
    private $priceHelper;
    /**
     * @var RuleCatalog
     */

    /**
     * RountripPrice constructor.
     * @param DataBookingSystem $dataBookingSystem
     * @param Registry $registry
     * @param PriceHelper $priceHelper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        DataBookingSystem $dataBookingSystem,
        Registry $registry,
        PriceHelper $priceHelper,
        Template\Context $context,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->dataBookingSystem = $dataBookingSystem;
        $this->registry = $registry;
        $this->priceHelper = $priceHelper;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function ifProductIsTransfer(){
        return $this->dataBookingSystem->ifCurrentProductIsTransfer();
    }

    /**
     * @return false|string
     */
    public function getJsonData(){
        $product = $this->registry->registry('product');
        $price = $this->priceHelper->currency($product->getRountripPrice(),false,false);
        $specialPrice = ($product->getRountripSpecialPrice()) ?
            $this->priceHelper->currency($product->getRountripSpecialPrice(),false,false)
            : $this->priceHelper->currency(0,false,false);

        $specialPrice = $this->dataBookingSystem->getPriceBetweenSpecialCalendarAndCatalogRule($product->getId(),$price,$specialPrice);

        if( $specialPrice > 0 ){
            $oldPrice = $price;
            $price = $specialPrice;
            $specialPrice = $oldPrice;
        }

        $arg = [
            'rountrip_price' => $price,
            'rountrip_old_price' => $specialPrice
        ];
        return json_encode($arg);
    }
}