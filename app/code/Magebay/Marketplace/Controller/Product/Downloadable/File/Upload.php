<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Product\Downloadable\File;

use Magento\Framework\Controller\ResultFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\MediaStorage\Helper\File\Storage\Database;

class Upload extends \Magebay\Marketplace\Controller\Product\Account
{
    /**
     * @var UploaderFactory
     */
    private $_uploaderFactory;

    /**
     * @var Database
     */
    private $_storageDatabase;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Downloadable\Helper\File $fileHelper
     * @param UploaderFactory $uploaderFactory 
     * @param Database $storageDatabase
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
        UploaderFactory $uploaderFactory,
        Database $storageDatabase
    ) {
        parent::__construct($context, $customerSession);
        $this->_uploaderFactory = $uploaderFactory;
        $this->_storageDatabase = $storageDatabase;
    }

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $type = $this->getRequest()->getParam('type');
        $tmpPath = '';
        if ($type == 'samples') {
            $tmpPath = $this->_objectManager->get('Magento\Downloadable\Model\Sample')->getBaseTmpPath();
        } elseif ($type == 'links') {
            $tmpPath = $this->_objectManager->get('Magento\Downloadable\Model\Link')->getBaseTmpPath();
        } elseif ($type == 'link_samples') {
            $tmpPath = $this->_objectManager->get('Magento\Downloadable\Model\Link')->getBaseSampleTmpPath();
        }

        try {
            $uploader = $this->_uploaderFactory->create(['fileId' => $type]);

            $result = $this->_objectManager->get('Magento\Downloadable\Helper\File')->uploadFromTmp($tmpPath, $uploader);
			$result = $this->__proccessDownload($result, $tmpPath);

        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
	
	/**
	 * @param array $result
	 * @param string $tmpPath
	 * @return array
	 */
	protected function __proccessDownload($result, $tmpPath) {
		if (!$result) {
			throw new \Exception('File can not be moved from temporary folder to the destination folder.');
		}

		$result['tmp_name'] = str_replace('\\', '/', $result['tmp_name']);
		$result['path'] = str_replace('\\', '/', $result['path']);

		if (isset($result['file'])) {
			$relativePath = rtrim($tmpPath, '/') . '/' . ltrim($result['file'], '/');
			$this->_storageDatabase->saveFile($relativePath);
		}
		return $result;
	}
}
