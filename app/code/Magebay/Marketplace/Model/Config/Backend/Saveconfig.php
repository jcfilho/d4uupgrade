<?php
/**
* @package    Magebay_Marketplace
* @version    2.0
* @author     Magebay Developer Team <magebay99@gmail.com>
* @website    https://www.magebay.com/magento-multi-vendor-marketplace-extension
* @copyright  Copyright (c) 2009-2016 MAGEBAY.COM. (http://www.magebay.com)
*/
namespace Magebay\Marketplace\Model\Config\Backend;

class Saveconfig extends \Magento\Framework\App\Config\Value
{
    protected $marketplaceHelper;
    protected $actModelFactory;
    protected $urlInterface;
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magebay\Marketplace\Helper\Data $marketplaceHelper,
        \Magebay\Marketplace\Model\ActFactory $actModelFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        array $data = []
    ) {
        $this->marketplaceHelper = $marketplaceHelper;
        $this->actModelFactory = $actModelFactory;
        $this->urlInterface = $urlInterface;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        $path = $this->getPath();
        $value = trim($this->getValue());
		//echo $value.'aa<br>';
		//echo $label.'bb<br>';
		$main_domain = $this->marketplaceHelper->get_domain( $_SERVER['HOSTNAME'] );
		$current_url = $this->urlInterface->getCurrentUrl();
		if ( $main_domain != 'dev' ) {  
            $url = base64_decode('aHR0cHM6Ly93d3cubWFnZWJheS5jb20vbXN0LnBocD9rZXk9').$value.'&domain='.$main_domain.'&server_name='.$current_url;
            //$file = file_get_contents($url);
            /* none ssl 
			$ch = curl_init(); 
            // set url 
            curl_setopt($ch, CURLOPT_URL, $url); 
            //return the transfer as a string 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            // $output contains the output string 
            $file = curl_exec($ch); 
            // close curl resource to free up system resources 
            curl_close($ch);  */
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_REFERER, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$file = curl_exec($ch);
			curl_close($ch);
			
            $get_content_id = $this->marketplaceHelper->get_div($file,"valid_licence");
            if(!empty($get_content_id[0])) {
                $return_valid = $get_content_id[0][0];
                if ( $return_valid == '1' ) {
                    $domain_count = $get_content_id[0][1];
                    $domain_list = $get_content_id[0][2];
                    $created_time = $get_content_id[0][3];
                    //echo $return_valid.'--'.$domain_count.'--'.$domain_list.'--'.$created_time;
                    $rakes = $this->actModelFactory->create()->getCollection();
                    $rakes->addFieldToFilter('path', 'marketplace/act/key' );
                    if ( count($rakes) > 0 ) {
                        foreach ( $rakes as $rake )  {
                            $update = $this->actModelFactory->create()->load( $rake->getActId() );
                            $update->setPath($path);
                            $update->setExtensionCode( md5($main_domain.$value) );
                            $update->setActKey($value);
                            $update->setDomainCount($domain_count);
                            $update->setDomainList($domain_list);
                            $update->setCreatedTime($created_time);
                            $update->save();
                        }
                    } else {  
                        $new = $this->actModelFactory->create();
                        $new->setPath($path);
                        $new->setExtensionCode( md5($main_domain.$value) );
                        $new->setActKey($value);
                        $new->setDomainCount($domain_count);
                        $new->setDomainList($domain_list);
                        $new->setCreatedTime($created_time);
                        $new->save();
                    }
                }
            }
		}
        $this->_cacheManager->clean();
        return parent::afterSave();
    }
}
