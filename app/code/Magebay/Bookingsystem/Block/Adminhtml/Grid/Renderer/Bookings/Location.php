<?php
namespace Magebay\Bookingsystem\Block\Adminhtml\Grid\Renderer\Bookings;
use \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magebay\Bookingsystem\Model\BookingsFactory;
use Magento\Directory\Model\Country;
class Location extends AbstractRenderer
{
	protected $_bookingModel;
	protected $_country;
	 public function __construct(
		BookingsFactory $bookingModel,
		Country $country
    ) {
		$this->_bookingModel = $bookingModel;
		$this->_country = $country;
    }
   public function render(\Magento\Framework\DataObject $row)
   {
		$collection = $this->_bookingModel->create();
		$collection = $collection->getCollection();
		$locaction = '';
		$productId = $this->_getValue($row);
		if($productId > 0)
		{
			$collection->addFieldToFilter('booking_product_id',$productId);
			if(count($collection))
			{
				foreach($collection as $collect)
				{
					if($collect->getId() && trim($collect->getBookingAddress()) != '')
					{
						$strCountry  = '';
						if($collect->getBookingCountry() != '')
						{
							$strCountry = $this->getBkCountryName($collect->getBookingCountry());
						}
						$locaction = $collect->getBookingAddress().', '.$collect->getBookingCity().', '.$strCountry;
					}
				}
			}
		}
		return $locaction;
   }
	function getBkCountryName($code)
	{
		
		$country = $this->_country->loadByCode($code);
		$name = '';
		if($country->getId())
		{
			$name = $country->getName();
		}
		return $name;
	}
}