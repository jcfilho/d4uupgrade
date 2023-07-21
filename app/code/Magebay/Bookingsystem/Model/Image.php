<?php
namespace Magebay\Bookingsystem\Model;

use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Image
{
    /**
     * media sub folder
     * @var string
     */
    protected $subDir = 'bookingsystem';

    /**
     * url builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
	/**
     * url builder
     *
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;
	/**
     * @var Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;
    /**
     * @param UrlInterface $urlBuilder
     * @param Filesystem $fileSystem
     */
    public function __construct(
        UrlInterface $urlBuilder,
		StoreManagerInterface $storeManager,
        Filesystem $fileSystem,
		AdapterFactory $imageFactory
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
        return $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]).$this->subDir.'/image';
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
		$absPath = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($this->subDir.'/image/').$image;
		$imageResized = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($this->subDir.'/image/resized/').$width.'x'.$height.'/'.$image;
		$imageResize = $this->_imageFactory->create();
		$imageResize->open($absPath);
		$imageResize->constrainOnly(TRUE);
		$imageResize->keepAspectRatio(TRUE);
		$imageResize->keepFrame(true);
		$imageResize->keepTransparency(TRUE);
		$imageResize->backgroundColor(array(255,255,255));
		$imageResize->resize($width,$height);
		$dest = $imageResized ;
		$imageResize->save($dest);
		$resizedURL = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA).$this->subDir.'/image/resized/'.$width.'x'.$height.'/'.$image;
		return $resizedURL;
	}
}