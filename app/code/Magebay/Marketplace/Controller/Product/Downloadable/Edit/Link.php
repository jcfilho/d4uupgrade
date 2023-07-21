<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Product\Downloadable\Edit;

class Link extends \Magebay\Marketplace\Controller\Product\Account
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
     * Download link action
     *
     * @return void
     */
    public function execute()
    {
        $linkId = $this->getRequest()->getParam('id', 0);
        $type = $this->getRequest()->getParam('type', 0);
        /** @var \Magento\Downloadable\Model\Link $link */
        $link = $this->_objectManager->create('Magento\Downloadable\Model\Link')->load($linkId);
        if ($link->getId()) {
            $resource = '';
            $resourceType = '';
            if ($type == 'link') {
                if ($link->getLinkType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_URL) {
                    $resource = $link->getLinkUrl();
                    $resourceType = \Magento\Downloadable\Helper\Download::LINK_TYPE_URL;
                } elseif ($link->getLinkType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE) {
                    $resource = $this->_objectManager->get(
                        'Magento\Downloadable\Helper\File'
                    )->getFilePath(
                        $this->_objectManager->get('Magento\Downloadable\Model\Link')->getBasePath(),
                        $link->getLinkFile()
                    );
                    $resourceType = \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE;
                }
            } else {
                if ($link->getSampleType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_URL) {
                    $resource = $link->getSampleUrl();
                    $resourceType = \Magento\Downloadable\Helper\Download::LINK_TYPE_URL;
                } elseif ($link->getSampleType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE) {
                    $resource = $this->_objectManager->get(
                        'Magento\Downloadable\Helper\File'
                    )->getFilePath(
                        $this->_objectManager->get('Magento\Downloadable\Model\Link')->getBaseSamplePath(),
                        $link->getSampleFile()
                    );
                    $resourceType = \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE;
                }
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
