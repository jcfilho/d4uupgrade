<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Model;

class Reviews extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    protected $_eventPrefix = 'magebay_marketplace';
    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'magebay_marketplace';
    protected $_url;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_url = $url;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magebay\Marketplace\Model\ResourceModel\Reviews');
    }

    /**
     * Retrieve model title
     * @param  boolean $plural
     * @return string
     */
    public function getOwnTitle($plural = false)
    {
        return $plural ? 'Seller\'s Reviews' : 'Seller\'s Reviews';
    }

    /**
     * Retrieve true if category is active
     * @return boolean [description]
     */
    public function isActive()
    {
        return ($this->getStatus() == self::STATUS_ENABLED);
    }

    /**
     * Retrieve available category statuses
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_DISABLED => __('Disabled'), self::STATUS_ENABLED => __('Enabled')];
    }
    
	function getMKReview($useId)
	{
		$data = array();
		if($useId > 0){
			$collection = $this->getCollection();
			$collection->addFieldToFilter('userid',$useId);
			$collection->addFieldToFilter('status',1);
			$price = 0;
			$value = 0;
			$quality = 0;
			$totalfeed = 0;
			if(count($collection) > 0)
			{
				foreach($collection as $record) {
					$price += $record->getPrice();
					$value += $record->getValue();
					$quality += $record->getQuality();
				}
				$totalfeed = ceil(($price+$value+$quality) / ( 3 *count($collection)));
			}
			$data = array(
					'price'=> $totalfeed > 0 ?  $price / count($collection) : 0,
					'value'=>$totalfeed > 0 ? $value/count($collection) : 0,
					'quality'=>$totalfeed > 0 ? $quality/count($collection) : 0,
					'totalfeed'=>$totalfeed,
					'feedcount'=>count($collection)
				);
			return $data;
		}
	}
}