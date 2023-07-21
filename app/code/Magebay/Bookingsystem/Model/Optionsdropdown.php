<?php
 
namespace Magebay\Bookingsystem\Model;
 
use Magento\Framework\Model\AbstractModel;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\Db;
use Magento\Framework\Data\Collection\AbstractDb;
use Magebay\Bookingsystem\Helper\BkText as BkHelperText;
 
class Optionsdropdown extends AbstractModel
{
	protected $_bkHelperText;
    /**
     * Define resource model
     */
	public function __construct(
		BkHelperText $bkHelperText,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
		$this->_bkHelperText = $bkHelperText;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
		
    }
    protected function _construct()
    {
        $this->_init('Magebay\Bookingsystem\Model\ResourceModel\Optionsdropdown');
    }
	/**
	* get values
	* param int $bookingId, $optionId
	* @return array $data
	**/
	function getBkValueOptions($optionId,$fieldSelect = array('*'))
	{
		$collection = $this->getCollection()
			->addFieldToSelect($fieldSelect)
			->addFieldToFilter('dropdown_option_id',$optionId);
		return $collection;
	}
	/* 
	* save values select options
	* @params array $params, int $optionId
	* return $this
	*/
	function saveBkSelectValue($params,$optionId)
	{
		//get values options by $optionId
		$collection = $this->getCollection()
				->addFieldToFilter('dropdown_option_id',$optionId);
		$rowIds = array();
		if(count($collection))
		{
			foreach($collection as $collect)
			{
				$rowIds[$collect->getId()] = $collect->getId();
			}
		}
		if(count($params))
		{
			foreach($params as $param)
			{
				$param['dropdown_option_id'] = $optionId;
				$title = isset($param['dropdown_title']) ? $param['dropdown_title'] : '';
				$isNew = true;
				$arCurrentTitle = array();
				$useDefault = 1;
				$defaultTitle = '';
				$storeId = isset($param['store_id']) ? $param['store_id'] : 0;
				if(array_key_exists($param['dropdown_id'],$rowIds))
				{
					unset($rowIds[$param['dropdown_id']]);
				}
				if($param['dropdown_id'] == 0)
				{
					unset($param['dropdown_id']);
				}
				else
				{
					$vaulesOption = $this->load($param['dropdown_id']);
					if($vaulesOption->getId())
					{
						$isNew = false;
						$defaultTitle = $vaulesOption->getDropdownTitle();
						if($vaulesOption->getDropdownTitleTranslate() != '')
						{
							$arCurrentTitle = $this->_bkHelperText->getBkJsonDecode($vaulesOption->getDropdownTitleTranslate());
						}
					}
				}
				if($storeId > 0)
				{
					unset($param['dropdown_title']);
					if(isset($param['use_default']))
					{
						
					}
					else
					{
						$useDefault = 0;
					}
				}
				
				$arTitleTrans = $this->_bkHelperText->getTextTranslate($title,$defaultTitle,$storeId,$isNew,$arCurrentTitle,$useDefault);
				$titleTras = $this->_bkHelperText->getBkJsonEncode($arTitleTrans);
				$param['dropdown_title_translate'] = $titleTras;
				$this->setData($param)->save();
			}
		}
		//delete data not set
		if($rowIds)
		{
			foreach($rowIds as $rowId)
			{
				$this->setId($rowId)->delete();
			}
		}
	}
	function deleteBkOptionsValues($optionId)
	{
		$collection = $this->getCollection()
				->addFieldToFilter('dropdown_option_id',$optionId);
		if(count($collection))
		{
			foreach($collection as $collect)
			{
				$this->setId($collect->getId())->delete();
			}
		}			
	}
}