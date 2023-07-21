<?php

namespace Daytours\Provider\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Daytours\Provider\Model\Provider;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Inspection\Exception;
use Daytours\Provider\Model\ProviderRepository;

class Save extends \Daytours\Provider\Controller\Adminhtml\Provider
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    /**
     * @var ProviderRepository
     */
    private $providerRepository;

    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param DataPersistorInterface $dataPersistor
     * @param ProviderRepository $providerRepository
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        DataPersistorInterface $dataPersistor,
        ProviderRepository $providerRepository
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context, $coreRegistry);
        $this->providerRepository = $providerRepository;
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('entity_id');

            if (empty($data['entity_id'])) {
                $data['entity_id'] = null;
            }

            /** @var \Daytours\Provider\Model\Provider $model */
            $model = $this->_objectManager->create(\Daytours\Provider\Model\Provider::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addError(__('This provider no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved the provider.'));
                $this->dataPersistor->clear('provider');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the provider.'));
            }

            $this->dataPersistor->set('provider', $data);
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $this->getRequest()->getParam('entity_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
