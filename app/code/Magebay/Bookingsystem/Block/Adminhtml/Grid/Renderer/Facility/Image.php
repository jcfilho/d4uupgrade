<?php
namespace Magebay\Bookingsystem\Block\Adminhtml\Grid\Renderer\Facility;
use \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magebay\Bookingsystem\Model\Image as ImageModel;
use Magebay\Bookingsystem\Model\FacilitiesFactory;

class Image extends AbstractRenderer
{
	protected $_imageModel;
	protected $_facilitiesFactory;
	 public function __construct(
		ImageModel $ImageModel,
		FacilitiesFactory $facilitiesFactory
    ) {
		$this->_imageModel = $ImageModel;
		$this->_facilitiesFactory = $facilitiesFactory;
    }
   public function render(\Magento\Framework\DataObject $row)
   {
		$baseUrl = $this->_imageModel->getBaseUrl();
		$id = $this->_getValue($row);
		$facilitiesModel = $this->_facilitiesFactory->create();
		$facility = $facilitiesModel->load($id);
		if($facility->getFacilityIconClass() != '')
		{
			$icon_class = explode('-', $facility->getFacilityIconClass() );
			$type_font = $icon_class[0];
			$strImage = '<i class="ace-icon '.$type_font.' '.$facility->getFacilityIconClass().' size-icon"></i>';
		}
		else
		{
			$strImage = '<img width="100" height="100" src="'.$baseUrl.'/'.$facility->getFacilityImage().'" />';
		}
		return $strImage;
   }
}