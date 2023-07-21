<?php
 
namespace Magebay\Bookingsystem\Helper;
 
use Magebay\Bookingsystem\Helper\Data;

class BkHelperDate extends Data
{
	/** 
	* convert format Date
	* @param string $date
	* @param string $date
	**/
	function convertFormatDate($strDate,$bkStore = true)
	{
		if($strDate == '')
			return $strDate;
		$formatDate =  $this->getFieldSetting('bookingsystem/setting/format_date',$bkStore);
		$arStrdate = array();
		$newStr = '';
		if($formatDate == 'm/d/Y')
		{
			$arStrdate = explode('/',$strDate);
			$newStr = $arStrdate[2].'-'.$arStrdate[0].'-'.$arStrdate[1];
		}
		elseif($formatDate == 'm-d-Y')
		{
			$arStrdate = explode('-',$strDate);
			$newStr = $arStrdate[2].'-'.$arStrdate[0].'-'.$arStrdate[1];
		}
		elseif($formatDate == 'd/m/Y')
		{
			$arStrdate = explode('/',$strDate);
			$newStr = $arStrdate[2].'-'.$arStrdate[1].'-'.$arStrdate[0];
		}
		elseif($formatDate == 'd-m-Y')
		{
			$arStrdate = explode('-',$strDate);
			$newStr = $arStrdate[2].'-'.$arStrdate[1].'-'.$arStrdate[0];
		}
		elseif($formatDate == 'Y/m/d')
		{
			$arStrdate = explode('/',$strDate);
			$newStr = $arStrdate[0].'-'.$arStrdate[1].'-'.$arStrdate[2];
		}
		elseif($formatDate == 'Y-m-d')
		{
			$newStr = $strDate;
		}
		elseif($formatDate == 'Y/d/m')
		{
			$arStrdate = explode('/',$strDate);
			$newStr = $arStrdate[0].'-'.$arStrdate[2].'-'.$arStrdate[1];
		}
		elseif($formatDate == 'Y-d-m')
		{
			$arStrdate = explode('-',$strDate);
			$newStr = $arStrdate[0].'-'.$arStrdate[2].'-'.$arStrdate[1];
		}
		return $newStr;
	}
	/** get format date label for jQquery Date
	* @return string $formatDate ;
	**/
	function getFormatDateLabel($bkStore = true)
	{
		$strFormat = '';
		$formatDate = $this->getFieldSetting('bookingsystem/setting/format_date',$bkStore);
		$arFormatDate = array();
		if(strstr($formatDate,'-'))
		{
			$arFormatDate = explode('-',$formatDate);
		}
		else
		{
			$arFormatDate = explode('/',$formatDate);
		}
		if(count($arFormatDate))
		{
			$i = 0;
			foreach($arFormatDate as $fr)
			{
				if($fr == 'Y')
				{
					$fr = 'yy';
				}
				elseif($fr == 'm')
				{
					$fr = 'mm';
				}
				elseif($fr == 'd')
				{
					$fr = 'dd';
				}
				if(strstr($formatDate,'-'))
				{
					if($i == 0)
					{
						$strFormat = $fr;
					}
					else
					{
						$strFormat .= '-'.$fr;
					}
				}
				else
				{
					if($i == 0)
					{
						$strFormat = $fr;
					}
					else
					{
						$strFormat .= '/'.$fr;
					}
				}
				$i++;
			}
		}
		return $strFormat;
	}
	/*
	 * get long format date lable
	 * @return string
	 * */
    function getLongFormatDateLabel($bkStore = true)
    {
        $strFormat = '';
        $formatDate = $this->getFieldSetting('bookingsystem/setting/format_date',$bkStore);
        $arFormatDate = array();
        if(strstr($formatDate,'-'))
        {
            $arFormatDate = explode('-',$formatDate);
        }
        else
        {
            $arFormatDate = explode('/',$formatDate);
        }
        if(count($arFormatDate))
        {
            $i = 0;
            foreach($arFormatDate as $fr)
            {
                if($fr == 'Y')
                {
                    $fr = 'yyyy';
                }
                elseif($fr == 'm')
                {
                    $fr = 'mm';
                }
                elseif($fr == 'd')
                {
                    $fr = 'dd';
                }
                if(strstr($formatDate,'-'))
                {
                    if($i == 0)
                    {
                        $strFormat = $fr;
                    }
                    else
                    {
                        $strFormat .= '-'.$fr;
                    }
                }
                else
                {
                    if($i == 0)
                    {
                        $strFormat = $fr;
                    }
                    else
                    {
                        $strFormat .= '/'.$fr;
                    }
                }
                $i++;
            }
        }
        return $strFormat;
    }
	/* 
	* get string day of week
	* @param string $day Y-m-d
	* @return string date
	*/
	function stringDay($date)
	{
		$tg = explode('-',$date);
		$mDay = $tg[2];
		if($mDay < 10)
		{
			$mDay = str_replace('0','',$mDay);
		}
		$mMonth = $tg[1];
		if($mMonth < 10)
		{
			$mMonth = str_replace('0','',$mMonth);
		}
		$jd = cal_to_jd(CAL_GREGORIAN,$mMonth,$mDay,$tg[0]);
		$day = jddayofweek($jd,0);
		$arText = array("sun","mon","tue","wed","thu","fri","sat");
		return $arText[$day]; 
	}
	/* 
	* check date format
	*/
	function validateBkDate($date,$format)
	{
		$d = \DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) == $date;
	}
}
 