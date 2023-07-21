<?php

namespace Magebay\Bookingsystem\Block\Adminhtml\Grid\Renderer\Facility;

use \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magebay\Bookingsystem\Helper\BkText;
use Magebay\Bookingsystem\Model\FacilitiesFactory;

class Title extends AbstractRenderer
{
	/**
     * Core Json Helper
     *
     * @var \Magebay\Bookingsystem\Model\FacilitiesFactory
     */
	protected $_facilitiesFactory;
	/**
     * Core Json Helper
     *
     * @var \Magebay\Bookingsystem\Helper\BkText
     */
	protected $_bkHelperText;
	/**
     * Core Json Helper
     *
     * @var \Magento\Framework\App\RequestInterface
     */
	protected $_request;
	
	public function __construct(
		BkText $bkHelperText,
		FacilitiesFactory $facilitiesFactory,
		\Magento\Framework\App\RequestInterface $requestInterface
    ) {
		$this->_bkHelperText = $bkHelperText;
		$this->_facilitiesFactory = $facilitiesFactory;
		$this->_request = $requestInterface;
    }
	
	public function render(\Magento\Framework\DataObject $row)
	{
		$strTitle = $this->_getValue($row);
		$storeId = $this->_request->getParam('store',0);
		if($storeId > 0)
		{
			$model = $this->_facilitiesFactory ->create();
			$id = $row->getId();
			$model->load($id);
			if($model->getFacilityTitle() != '' && $model->getFacilityTitleTransalte() != '')
			{
				$strTitle .= ' <br/>('.$this->_bkHelperText->showTranslateText($model->getFacilityTitle(),$model->getFacilityTitleTransalte(),$storeId).')';
			}
		}
		return $strTitle;
	}
}