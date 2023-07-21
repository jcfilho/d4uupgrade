<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Model;
use Magento\Framework\UrlInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
class Image
{
    /**
     * media sub folder
     * @var string
     */
    protected $subDir = 'marketplace';
    /**
     * url builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;
    /**
     * @param UrlInterface $urlBuilder
     * @param Filesystem $fileSystem
     */
    public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        Filesystem $fileSystem,
		\Magento\Framework\Image\AdapterFactory $imageFactory
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->fileSystem = $fileSystem;
		$this->_storeManager = $storeManager;
        $this->_imageFactory = $imageFactory;
    }
    /**
     * get images base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]).$this->subDir.'/';
    }
	public function getPathUrl()
    {
        return $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath($this->subDir.'/');
    }
	public function getPathMedia()
    {
        return $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath();
    }
    /**
     * get base image dir
     *
     * @return string
     */
    public function getBaseDir()
    {
        return $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath($this->subDir.'/image');
    }
	public function imageResize($image,$width,$height)
	{
		//$absPath = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($this->subDir.'/image/').$image;
		$absPath = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath().$image;
		//echo $absPath; exit();
		$imageResized = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($this->subDir.'/image/resized/').$image;
		if (file_exists(  $imageResized ) and $image != '' ) {
			$resizedURL = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA).$this->subDir.'/image/resized/'.$image;
			return $resizedURL;
		}
		$imageResize = $this->_imageFactory->create();
		$imageResize->open($absPath);
		$imageResize->constrainOnly(TRUE);
		$imageResize->keepAspectRatio(true);
		$imageResize->keepFrame(true);
		$imageResize->keepTransparency(TRUE);
		$imageResize->backgroundColor(array(255,255,255));
		$imageResize->resize($width,$height);
		$dest = $imageResized ;
		$imageResize->save($dest);
		$resizedURL = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA).$this->subDir.'/image/resized/'.$image;
		return $resizedURL;
	}
}