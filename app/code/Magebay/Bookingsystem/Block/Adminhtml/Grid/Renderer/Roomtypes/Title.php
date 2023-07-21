<?php

namespace Magebay\Bookingsystem\Block\Adminhtml\Grid\Renderer\Roomtypes;

use \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magebay\Bookingsystem\Helper\BkText;
use Magebay\Bookingsystem\Model\RoomtypesFactory;

class Title extends AbstractRenderer
{
	/**
     * Core Json Helper
     *
     * @var \Magebay\Bookingsystem\Model\RoomtypesFactory
     */
	protected $_roomtypesFactory;
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
		RoomtypesFactory $roomtypesFactory,
		\Magento\Framework\App\RequestInterface $requestInterface
    ) {
		$this->_bkHelperText = $bkHelperText;
		$this->_roomtypesFactory = $roomtypesFactory;
		$this->_request = $requestInterface;
    }
	
	public function render(\Magento\Framework\DataObject $row)
	{
		$strTitle = $this->_getValue($row);
		$storeId = $this->_request->getParam('store',0);
		if($storeId > 0)
		{
			$model = $this->_roomtypesFactory ->create();
			$id = $row->getId();
			$model->load($id);
			if($model->getRoomtypeTitle() != '' && $model->getRoomtypeTitleTransalte() != '')
			{
				$strTitle .= ' <br/>('.$this->_bkHelperText->showTranslateText($model->getRoomtypeTitle(),$model->getRoomtypeTitleTransalte(),$storeId).')';
			}
		}
		return $strTitle;
	}
}