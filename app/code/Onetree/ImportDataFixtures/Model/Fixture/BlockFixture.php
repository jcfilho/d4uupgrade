<?php

namespace Onetree\ImportDataFixtures\Model\Fixture;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class BlockFixture
 * @package Onetree\ImportDataFixtures\Model\Fixture
 */
class BlockFixture extends AbstractFixture
{
    const KEY_IDENTIFIER = 'identifier';

    const KEY_FIXTURE_ID = 'block_id';

    const KEY_FIXTURE_TYPE = 'cms_block';

    const KEY_STORE_ID = 'store_id';

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $blockFactory;
    /**
     * @var \Magento\Cms\Model\BlockRepository
     */
    protected $blockRepository;
    /**
     * @var \Magento\Cms\Model\ResourceModel\Block
     */
    protected $blockResourceModel;
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;
    /**
     * @var \Onetree\ImportDataFixtures\Model\Block\ResourceModel\Block
     */
    private $blockResourceModelCustom;

    /**
     * BlockFixture constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param \Magento\Cms\Model\BlockRepository $blockRepository
     * @param \Magento\Cms\Model\ResourceModel\Block $blockResourceModel
     * @param \Onetree\ImportDataFixtures\Model\Block\ResourceModel\Block $blockResourceModelCustom
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Cms\Model\BlockRepository $blockRepository,
        \Magento\Cms\Model\ResourceModel\Block $blockResourceModel,
        \Onetree\ImportDataFixtures\Model\Block\ResourceModel\Block $blockResourceModelCustom,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($objectManager);

        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
        $this->blockResourceModel = $blockResourceModel;
        $this->dataPersistor = $dataPersistor;
        $this->blockResourceModelCustom = $blockResourceModelCustom;
    }

    /**
     * @param $data
     * @return bool
     */
    protected function installData($data)
    {
        $this->logger->debug("[BlockFixture][InstallData]", $data);
        /** @var \Magento\Cms\Model\Block $model */
        $model = $this->blockFactory->create();
        try {
            if (isset($data[self::KEY_FIXTURE_ID]) && !empty($data[self::KEY_FIXTURE_ID])) {
                $model = $this->blockRepository->getById($data[self::KEY_FIXTURE_ID]);
            } else if (isset($data[self::KEY_IDENTIFIER])) {
                // update $data if there are store id
                if (isset($data[self::KEY_STORE_ID]) && !empty($data[self::KEY_STORE_ID])) {
                    $model->setStoreId($data[self::KEY_STORE_ID]);
                }
                $this->blockResourceModelCustom->load($model, $data[self::KEY_IDENTIFIER], 'identifier');
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->addError(__('This block no longer exists.'));
        }

        try {
            if ($model->getId()) {
                $data[self::KEY_FIXTURE_ID] = $model->getId();
            }
            $model->setData($data);
            $model = $this->blockRepository->save($model);
            $this->dataPersistor->clear(self::KEY_FIXTURE_TYPE);
        } catch (LocalizedException $e) {
            $this->logger->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->addError($e, __('Something went wrong while saving the block.'));
        }

        return ($model->getId()) ? true : false;
    }
}