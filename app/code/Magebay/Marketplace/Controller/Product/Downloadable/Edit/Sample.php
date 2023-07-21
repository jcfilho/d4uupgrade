<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Product\Downloadable\Edit;

class Sample extends \Magebay\Marketplace\Controller\Product\Account
{
	/**
	 * @param \Magento\Framework\App\Action\Context $context
	 * @param \Magento\Customer\Model\Session $customerSession
	 */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context, $customerSession);
    }
	
    /**
     * Download sample action
     *
     * @return void
     */
    public function execute()
    {
        $sampleId = $this->getRequest()->getParam('id', 0);
        /** @var \Magento\Downloadable\Model\Sample $sample */
        $sample = $this->_objectManager->create('Magento\Downloadable\Model\Sample')->load($sampleId);
        if ($sample->getId()) {
            $resource = '';
            $resourceType = '';
            if ($sample->getSampleType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_URL) {
                $resource = $sample->getSampleUrl();
                $resourceType = \Magento\Downloadable\Helper\Download::LINK_TYPE_URL;
            } elseif ($sample->getSampleType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE) {
                $resource = $this->_objectManager->get(
                    'Magento\Downloadable\Helper\File'
                )->getFilePath(
                    $this->_objectManager->get('Magento\Downloadable\Model\Sample')->getBasePath(),
                    $sample->getSampleFile()
                );
                $resourceType = \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE;
            }
            try {
				/* @var $helper \Magento\Downloadable\Helper\Download */
				$helper = $this->_objectManager->get('Magento\Downloadable\Helper\Download');
				$helper->setResource($resource, $resourceType);

				$fileName = $helper->getFilename();
				$contentType = $helper->getContentType();

				$this->getResponse()->setHttpResponseCode(200)
					->setHeader('Pragma','public',true)
					->setHeader('Cache-Control','must-revalidate, post-check=0, pre-check=0', true)
					->setHeader('Content-type',$contentType,true);
				if ($fileSize = $helper->getFileSize()) {
					$this->getResponse()->setHeader('Content-Length', $fileSize);
				}

				if ($contentDisposition = $helper->getContentDisposition()) {
					$this->getResponse()
						->setHeader('Content-Disposition', $contentDisposition . '; filename=' . $fileName);
				}

				$this->getResponse()->clearBody();
				$this->getResponse()->sendHeaders();
				$helper->output();				
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError(__('Something went wrong while getting the requested content.'));
            }
        }
    }
}
