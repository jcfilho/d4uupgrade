<?php
 
namespace Magebay\Bookingsystem\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
class Data extends AbstractHelper
{
   
	const XML_PATH_ENABLED = 'bookingsystem/setting/enable';
	protected $_backendHelper;
    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     */
	protected $_stores;
	/**
     * Core Json Helper
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
	protected $_jsonHelper;
    public function __construct(
       Context $context,
	   BackendHelper $backendHelper,
	   StoreManagerInterface $storeManagerInterface,
	   JsonHelper $jsonHelper
    ) 
	{
       parent::__construct($context);
       $this->_backendHelper = $backendHelper;
       $this->_stores = $storeManagerInterface;
       $this->_jsonHelper = $jsonHelper;
    }
   /**
     * Check for module is enabled in frontend
     *
     * @return bool
     */
	public function isEnabledInFrontend($store = null)
   {
      return $this->scopeConfig->getValue(
         self::XML_PATH_ENABLED,
         ScopeInterface::SCOPE_STORE
      );
	}
	/* 
	* get field setting
	* 
	*/
	function getFieldSetting($field,$bkStore = true)
	{
		$filedSetting = $this->scopeConfig->getValue($field,ScopeInterface::SCOPE_STORE); 
		if(!$bkStore)
		{
			$filedSetting = $this->scopeConfig->getValue($field,ScopeInterface::SCOPE_WEBSITES);
		}
		return $filedSetting;
	}
   /* 
	* get All store In Magento
   */
   function getBkStores()
   {
	   return $this->_stores->getStores();
   }
   /**
	* get store 
   **/
   function getBkStore($storeId)
   {
	   return $this->_stores->getStore($storeId);
   }
   /** 
	* get current storeId
   **/
   function getbkCurrentStore()
   {
	   return $this->_stores->getStore()->getStoreId();
   }
   function getBkCurrencyCode()
   {
	   return $this->_stores->getStore()->getCurrentCurrencyCode();
   }
   function getBkJsonEncode($array = array())
   {
	   $strJson = '';
	   if(count($array))
	   {
		   $strJson = $this->_jsonHelper->jsonEncode($array);
	   }
	   return $strJson;
   }
   function getBkJsonDecode($str = '')
   {
	   $array = array();
	   if($str != '')
	   {
		   $array  = $this->_jsonHelper->jsonDecode($str);
	   }
	   return $array;
   }
   /**
	* format Url For ajax
	* @param string $url
	* @return string $url 
	**/
	function formatUrlPro($url_request)
	{
		$http_mode	= (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on');
		if($http_mode)
			$url_request = str_replace('http:', 'https:', $url_request);
		return $url_request;
	}
	/** 
	* get Url in Backend
	* @param string $url
	* @return string $url 
	**/
	function getBkAdminAjaxUrl($router,$param = [])
	{
	   $url =  $this->_backendHelper->getUrl($router,$param);
	   $url =  $this->formatUrlPro($url);
	   return $url;
	}
	/** 
	* get Url in front end
	* @param string $url
	* @return string $url 
	**/
	function getBkFrontendAjaxUrl($url)
	{
		$url =  $this->formatUrlPro($url);
	   return $url;
	}
		/** Get store config data in system**/
    public function getStoreConfigData($path) {
         $config = $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
         return $config;
    }
    function getTextTimeHours($intTime)
    {
        $h = date('H',$intTime);
        $m = date('i',$intTime);
        $timeModel = $this->getFieldSetting('bookingsystem/setting/time_mode');
        $textTime = $h.':'.$m;
        if($timeModel == 1)
        {
            $typeTime = $h >= 12 ? __('PM') : __('AM');
            $h = $h > 12 ? $h - 12 : (int)$h;
            $h = $h < 10 ? '0'.$h : $h;
            $textTime = $h.':'.$m.' '.$typeTime;
        }
        return $textTime;
    }
	public function get_content_id($file,$id){
		$h1tags = preg_match_all("/(<div id=\"{$id}\">)(.*?)(<\/div>)/ismU",$file,$patterns);
		$res = array();
		array_push($res,$patterns[2]);
		array_push($res,count($patterns[2]));
		return $res;
	}
	public function get_div($file,$id){
	    $h1tags = preg_match_all("/(<div.*>)(\w.*)(<\/div>)/ismU",$file,$patterns);
	    $res = array();
	    array_push($res,$patterns[2]);
	    array_push($res,count($patterns[2]));
	    return $res;
	}
    public function get_domain($url)   {   
		//$dev = 'dev';
		$dev = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HOSTNAME'];
		if ( !preg_match("/^http/", $url) )
			$url = 'http://' . $url;
		if ( $url[strlen($url)-1] != '/' )
			$url .= '/';
		$pieces = parse_url($url);
		$domain = isset($pieces['host']) ? $pieces['host'] : ''; 
		if ( preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs) ) { 
			$res = preg_replace('/^www\./', '', $regs['domain'] );
			return $res;
		}   
		return $dev;
	}
	/*
	 * Check Standard version
	 * */
	function  checkStandardVersion()
    {
        $roomFile = BP.'/app/code/Magebay/Bookingsystem/view/frontend/templates/product/view/bk_room_detail.phtml';
        $listRoom = BP.'/app/code/Magebay/Bookingsystem/view/frontend/templates/product/view/bk_rooms.phtml';
        $tourForm =  BP.'/app/code/Magebay/Bookingsystem/view/frontend/templates/product/view/bk_form_tour.phtml';
        $tourJS =  BP.'/app/code/Magebay/Bookingsystem/view/frontend/templates/product/view/bk_content_tour.phtml';
        $ok = false;
        if(file_exists($roomFile) && file_exists($listRoom) && file_exists($tourForm) && file_exists($tourJS))
        {
            $ok = true;
        }
        return $ok;
    }
}
 