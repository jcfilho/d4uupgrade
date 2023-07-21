<?php

namespace Onetree\ImportDataFixtures\Model\Fixture;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;

/**
 * Class PageFixture
 * @package Onetree\ImportDataFixtures\Model\Fixture
 */
class PageFixture extends AbstractFixture
{
    const KEY_IDENTIFIER = 'identifier';

    const KEY_FIXTURE_ID = 'page_id';

    const KEY_FIXTURE_TYPE = 'cms_page';

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $pageFactory;
    /**
     * @var \Magento\Cms\Model\PageRepository
     */
    protected $pageRepository;
    /**
     * @var \Magento\Cms\Model\ResourceModel\Page
     */
    private $pageResourceModel;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    /**
     * @var Page\PostDataProcessor
     */
    private $dataProcessor;
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * PageFixture constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Cms\Model\PageRepository $pageRepository
     * @param \Magento\Cms\Model\ResourceModel\Page $pageResourceModel
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param Page\PostDataProcessor $dataProcessor
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Model\PageRepository $pageRepository,
        \Magento\Cms\Model\ResourceModel\Page $pageResourceModel,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Page\PostDataProcessor $dataProcessor,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($objectManager);

        $this->pageFactory = $pageFactory;
        $this->pageRepository = $pageRepository;
        $this->pageResourceModel = $pageResourceModel;
        $this->eventManager = $eventManager;
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @param $data
     * @return bool
     */
    protected function installData($data)
    {
        $this->logger->debug("[PageFixture][InstallData]", $data);

        /** @var \Magento\Cms\Model\Page $model */
        $model = $this->pageFactory->create();
        if (isset($data[self::KEY_FIXTURE_ID]) && !empty($data[self::KEY_FIXTURE_ID])) {
            $id = $data[self::KEY_FIXTURE_ID];
            if ($id) {
                try {
                    $model = $this->pageRepository->getById($id);
                } catch (LocalizedException $e) {
                    $this->logger->addError(__('This page no longer exists.'));
                }
            }
        } else if (isset($data[self::KEY_IDENTIFIER])) {
            $this->pageResourceModel->load($model, $data[self::KEY_IDENTIFIER], self::KEY_IDENTIFIER);
        }

        try {
            if ($model->getId()) {
                $data['page_id'] = $model->getId();
            }
            $model->setData($data);
            $this->eventManager->dispatch(
                'cms_page_prepare_save',
                ['page' => $model, 'request' => null]
            );
            if (!$this->dataProcessor->validate($data)) {
                $messages = $this->dataProcessor->getMessageManager()->getMessages();
                foreach ($messages as $message) {
                    $this->logger->addError($message);
                }

                throw new ValidatorException(new \Magento\Framework\Phrase("The {$model->getTitle()} page can't be saved."));
            }

            $model = $this->pageRepository->save($model);
            $this->dataPersistor->clear(self::KEY_FIXTURE_TYPE);
        } catch (LocalizedException $e) {
            $this->logger->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->addError($e, __('Something went wrong while saving the page.'));
        }

        return ($model->getId()) ? true : false;
    }
}