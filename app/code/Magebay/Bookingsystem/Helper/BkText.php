<?php
 
namespace Magebay\Bookingsystem\Helper;
 
use Magebay\Bookingsystem\Helper\Data;

class BkText extends Data
{
	/**
	* show translate text for each store
	* @params $text, $textTran, $storeId
	* @return $string $textTranslate;
	**/
	function showTranslateText($text,$textTrans,$storeId = null)
	{
		if($storeId === null)
		{
			$storeId = $this->getbkCurrentStore();;
		}
		$finalText = $text;
		if($storeId > 0)
		{
			if($textTrans != '')
			{
				$arTexts = $this->getBkJsonDecode($textTrans);
				if(count($arTexts))
				{
					foreach($arTexts  as $arText)
					{
						if($arText['store_id'] == $storeId)
						{
							$finalText = $arText['value'];
							break;
						}
					}
				}
			}
		}
		return $finalText;
	}
	function getBkArTextByStore($text,$textTrans,$storeId = null)
	{
		if($storeId === null)
		{
			$storeId = $this->getbkCurrentStore();;
		}
		$arrayText = array('value'=>$text,'use_default'=>1);
		if($storeId > 0)
		{
			if($textTrans != '')
			{
				$arTexts = $this->getBkJsonDecode($textTrans);
				if(count($arTexts))
				{
					foreach($arTexts  as $arText)
					{
						if($arText['store_id'] == $storeId)
						{
							$arrayText = $arText;
							break;
						}
					}
				}
			}
		}
		return $arrayText;
	}
	/**
	* create array text to translate field.
	* @params string $text (input value), $textDefault (get from database), boolean $isnew (define add or update)
	* int $storeId , array $arCurrentText (get from database) int $useDfault = 1
	* @return array $arText;
	**/
   	function getTextTranslate($text,$textDefault,$storeId,$isNew = true,$arCurrentText = array(),$useDfault = 1)
	{
		$allStores = $this->getBkStores();
		$arText = array();
		//create array allStores
		$arStoreIds = array();
		foreach ($allStores as $_eachStoreId3 => $valBa)
		{
			$_storeId3 = $this->getBkStore($_eachStoreId3)->getId();
			$arStoreIds[$_storeId3] = $_storeId3;
		}
		if($storeId == 0)
		{
			//if add at default, create array for all store
			if($isNew || !count($arCurrentText))
			{
				foreach ($allStores as $_eachStoreId => $val)
				{
					$_storeId = $this->getBkStore($_eachStoreId)->getId();
					$arText[] = array(
						'store_id'=>$_storeId,
						'use_default'=>1,
						'value'=>$text
					);
				}
			}
			//if update at default, update all data store use default value
			else
			{
				foreach ($allStores as $_eachStoreId => $val)
				{
					$_storeId = $this->getBkStore($_eachStoreId)->getId();
					if(count($arCurrentText) > 0)
					{
						foreach($arCurrentText as $key => $tempText)
						{
							if($tempText['store_id'] == $_storeId && $tempText['use_default'] == '1')
							{
								$arCurrentText[$key]['value'] = $text;
								//break;
							}
							if($tempText['store_id'] == $_storeId)
							{
								unset($arStoreIds[$_storeId]);
							}
						}
					}
				}
				$arText = $arCurrentText;
				if(count($arStoreIds))
				{
					foreach($arStoreIds as $arStoreId)
					{
						$arText[] = array(
							'store_id'=>$arStoreId,
							'use_default'=>1,
							'value'=>$text
						);
					}
				}
			}
		}
		else
		{
			//if add at store view
			if($isNew || !count($arCurrentText))
			{
				foreach ($allStores as $_eachStoreId => $val)
				{
					$_storeId = $this->getBkStore($_eachStoreId)->getId();
					//set curent store that update use default value is 0
					if($_storeId == $storeId)
					{
						$arText[] = array(
						'store_id'=>$_storeId,
						'use_default'=>0,
						'value'=>$text
						);
					}
					else
					{
						$arText[] = array(
							'store_id'=>$_storeId,
							'use_default'=>1,
							'value'=>$text
						);
					}
				}
			}
			else
			{
				foreach ($allStores as $_eachStoreId => $val)
				{
					$_storeId = $this->getBkStore($_eachStoreId)->getId();
					//only update at current store view
					if($_storeId == $storeId)
					{
						$storeExit = false;
						foreach($arCurrentText as $key2 => $tempText2)
						{
							if($tempText2['store_id'] == $storeId)
							{
								$storeExit  = true;
								$arCurrentText[$key2]['use_default'] = $useDfault;
								if($useDfault == 1)
								{
									$arCurrentText[$key2]['value'] = $textDefault;
								}
								else
								{
									$arCurrentText[$key2]['value'] = $text;
								}
								break;
							}
						}
						$arText = $arCurrentText;
						if(!$storeExit)
						{
							$tempText = $text != '' ? $text  : $textDefault;
							$arText[] = array(
								'store_id'=>$_storeId,
								'use_default'=>1,
								'value'=>$tempText
							);
						}
						break;
					}
				}
			}
		}
		return $arText;
	}
	function cutDescription($value,$number)
	{
		$str = '';
		if(strlen($value) > $number)
        {
            $strCut = substr($value,0,$number);
            $str = substr($strCut,0,strrpos($strCut,' ')).'...';
        }
        else
        {
            $str = $value;
        }
		return $str;
	}
}
 