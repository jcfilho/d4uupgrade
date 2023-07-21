<?php

namespace Daytours\Bookingsystem\Block\Adminhtml;
 
use Magento\Backend\Block\Template;
use Magebay\Bookingsystem\Helper\Data as BkHelper;

class RentPopup extends \Magebay\Bookingsystem\Block\Adminhtml\RentPopup
{
	/**
     * @param \Magebay\Bookingsystem\Helper\Data
     * 
     */
	protected $_bkHelper;
    /**
     * @var \Magebay\Bookingsystem\Model\BookingsFactory
     */
    protected $_bookingFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /** @var \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet **/
    protected $_attributeSet;

    protected $_transfer;

    /**
     * RentPopup constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param BkHelper $bkHelper
     * @param \Magebay\Bookingsystem\Model\BookingsFactory $bookingFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet
     * @param \Daytours\Bookingsystem\Block\Transfer $transfer
     * @param array $data
     * @internal param \Magebay\Bookingsystem\Model\BookingsFactory $booking
     */
	function __construct(
		\Magento\Backend\Block\Widget\Context $context,
		BkHelper $bkHelper,
        \Magebay\Bookingsystem\Model\BookingsFactory $bookingFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet,
        \Daytours\Bookingsystem\Block\Transfer $transfer,
		array $data = []
	)
	{

        $this->_bookingFactory = $bookingFactory;
        $this->_productFactory = $productFactory;
        $this->_attributeSet = $attributeSet;
        $this->_transfer = $transfer;
        $this->_bkHelper = $bkHelper;
		parent::__construct($context,$bkHelper, $data);
	}
	function getBkRequestData()
	{
		$bookingId = $this->getBookingId();
		$bookingType = $this->getBookingType();
		$bookingTime = $this->getBookingTime();
		return array(
			'booking_id'=>$bookingId,
			'booking_type'=>$bookingType,
			'booking_time'=>$bookingTime
		);
	}

}