<?php
 
namespace Magebay\Bookingsystem\Model;
 
use Magento\Framework\Model\AbstractModel;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\Db;
use Magento\Framework\Data\Collection\AbstractDb;
use Magebay\Bookingsystem\Model\Optionsdropdown;
use Magebay\Bookingsystem\Helper\BkText as BkHelperText;

class Options extends AbstractModel
{
	protected $_bkHelperText;
	protected $_optionsdropdown;
    /**

     * @param IsActive $statusList
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
		Optionsdropdown $optionsdropdown,
		BkHelperText $bkHelperText,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
		$this->_optionsdropdown = $optionsdropdown;
		$this->_bkHelperText = $bkHelperText;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
		
    }
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Magebay\Bookingsystem\Model\ResourceModel\Options');
    }
	/* 
	* get options by bookingId
	* @param int $bookingId
	* @return $items
	*/
	function getBkOptions($bookingId,$bookingType = 'per_day',$fieldSelect = array('*'),$sortOrder = 'ASC')
	{
		$collection = $this->getCollection()
			->addFieldToSelect($fieldSelect)
			->addFieldToFilter('option_booking_id',$bookingId)
			->addFieldToFilter('option_booking_type',$bookingType)
			->setOrder('option_sort',$sortOrder);
		return $collection;
	}
	/* 
	* get options by bookingId
	* @param int $bookingId
	* @return array $data
	*/
	function getBkOptionsData($bookingId,$bookingType = 'per_day',$fieldSelect = array('*'),$sortOrder = 'ASC')
	{
		$collection = $this->getBkOptions($bookingId,$bookingType,$fieldSelect,$sortOrder);
		$arData = array();
		$storeId = $this->_bkHelperText->getbkCurrentStore();
		if(count($collection))
		{
			foreach($collection as $key =>  $collect)
			{
				$arData[$key] = $collect->getData();
				$title = $this->_bkHelperText->showTranslateText($collect->getOptionTitle(),$collect->getOptionTitleTranslate(),$storeId);
				$arData[$key]['option_title'] = $title;
			}
		}
		return $arData;
	}
	/**
	* save data options
	* @param array $inputs, int $bookingId
	**/
	function saveBkOptions($params,$bookingId,$bookingType = 'per_day')
	{
		$collection = $this->getBkOptions($bookingId,$bookingType,array('option_id'));
		$optionIds = array();
		$selectModel = $this->_optionsdropdown;
		if(count($collection))
		{
			foreach($collection as $collect)
			{
				$optionIds[$collect->getId()] = $collect->getId();
			}
		}
		$okDelete = true;
		if(count($params))
		{
			foreach($params as $mkey => $param)
			{
				$param['option_booking_id'] = $bookingId;
				$title = isset($param['option_title']) ? $param['option_title'] : '';
				$paramValueOption = isset($param['values']) ? $param['values'] : array();
				if($param['option_type'] == 2 || $param['option_type'] == 3 || $param['option_type'] == 4 || $param['option_type'] == 5)
				{
					if(!count($paramValueOption))
					{
						throw new \Exception(__('Please enter values'));
						$okDelete = false;
						return false;
					}
				}
				$storeId = isset($param['store_id']) ? $param['store_id'] : 0;
				$isNew = true;
				$arCurrentTitle = array();
				$useDefault = 1;
				$defaultTitle = '';
				if(array_key_exists($param['option_id'],$optionIds))
				{
					unset($optionIds[$param['option_id']]);
				}
				if($param['option_id'] == 0)
				{
					unset($param['option_id']);
					
				}
				else
				{
					$optionModel = $this->load($param['option_id']);
					if($optionModel->getId())
					{
						$isNew = false;
						$defaultTitle = $optionModel->getOptionTitle();
						if($optionModel->getOptionTitleTranslate() != '')
						{
							$arCurrentTitle = $this->_bkHelperText->getBkJsonDecode($optionModel->getOptionTitleTranslate());
						}
					}
				}
				if($storeId > 0)
				{
					unset($param['option_title']);
					if(isset($param['use_default']))
					{
						
					}
					else
					{
						$useDefault = 0;
					}
				}
				if((trim($title) == '' && $useDefault == 0) || ($storeId == 0 && trim($title) == ''))
				{
					throw new \Exception(__('Please enter title'));
					$okDelete = false;
					return false;
				}
				$param['option_booking_type'] = $bookingType;
				$arTitleTrans = $this->_bkHelperText->getTextTranslate($title,$defaultTitle,$storeId,$isNew,$arCurrentTitle,$useDefault);
				$titleTras = $this->_bkHelperText->getBkJsonEncode($arTitleTrans);
				$param['option_title_translate'] = $titleTras;
				$this->setData($param)->save();
				$selectModel->saveBkSelectValue($paramValueOption,$this->getId());
			}
		}
		//delete old options
		if(count($optionIds) && $okDelete)
		{
			foreach($optionIds as $optionId)
			{
				$selectModel->deleteBkOptionsValues($optionId);
				$this->setId($optionId)->delete();
			}
		}
	}
	function deleteAddonOptions($bookingId,$bookingType)
	{
		$selectModel = $this->_optionsdropdown;
		$collection = $this->getCollection()
			->addFieldToFilter('option_booking_id',$bookingId)
			->addFieldToFilter('option_booking_type',$bookingType);
		if(count($collection))
		{
			foreach($collection as $collect)
			{
				$optionId = $collect->getId();
				$selectModel->deleteBkOptionsValues($optionId);
				$this->setId($optionId)->delete();
			}
		}
	}
}