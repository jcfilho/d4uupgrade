<?php
/**
 * @package    Magebay_Bookingsystem
 * @version    2.0
 * @author     Magebay Developer Team <magebay99@gmail.com>
 * @website    http://www.productsdesignercanvas.com
 * @copyright  Copyright (c) 2009-2016 MAGEBAY.COM. (http://www.magebay.com)
 */
namespace Magebay\Bookingsystem\Model\Config\Backend;
class Saveconfig extends \Magento\Framework\App\Config\Value
{
    protected $bookingsystemHelper;
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
        \Magebay\Bookingsystem\Helper\Data $bookingsystemHelper,
        \Magebay\Bookingsystem\Model\ActFactory $actModelFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        array $data = []
    ) {
        $this->bookingsystemHelper = $bookingsystemHelper;
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
        //$main_domain = $this->bookingsystemHelper->get_domain( $_SERVER['HOSTNAME'] );
        $main_domain = $this->bookingsystemHelper->get_domain( 'https://www.daytours4u.com/' );
        $current_url = $this->urlInterface->getCurrentUrl();
        if ( $main_domain != 'dev' ) {
            $url = base64_decode('aHR0cHM6Ly9ib29raW5nc3lzdGVtcHJvLmNvbS9tc3QucGhwP2tleT0=').$value.'&domain='.$main_domain.'&server_name='.$current_url;
            //$file = file_get_contents($url);
            $ch = curl_init();
            // set url
            curl_setopt($ch, CURLOPT_URL, $url);
            //return the transfer as a string
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            // $output contains the output string
            $file = curl_exec($ch);
            // close curl resource to free up system resources
            curl_close($ch);
            $get_content_id = $this->bookingsystemHelper->get_div($file,"valid_licence");

            //print_r( $get_content_id[0] );
            //exit();

            if(empty($get_content_id[0]) or $get_content_id[0][0] != '1' ) {
                //echo 'keke';
                //exit();
                $url = base64_decode('aHR0cHM6Ly93d3cubWFnZWJheS5jb20vbXN0LnBocD9rZXk9').$value.'&domain='.$main_domain.'&server_name='.$current_url;
                //$file = file_get_contents($url);
                $ch = curl_init();
                // set url
                curl_setopt($ch, CURLOPT_URL, $url);
                //return the transfer as a string
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                // $output contains the output string
                $file = curl_exec($ch);
                // close curl resource to free up system resources
                curl_close($ch);
                $get_content_id = $this->bookingsystemHelper->get_div($file,"valid_licence");
            }

            if(!empty($get_content_id[0])) {
                $return_valid = $get_content_id[0][0];
                if ( $return_valid == '1' ) {
                    $domain_count = $get_content_id[0][1];
                    $domain_list = $get_content_id[0][2];
                    $created_time = $get_content_id[0][3];
                    //echo $return_valid.'--'.$domain_count.'--'.$domain_list.'--'.$created_time;
                    $rakes = $this->actModelFactory->create()->getCollection();
                    $rakes->addFieldToFilter('path', 'bookingsystem/act/key' );
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