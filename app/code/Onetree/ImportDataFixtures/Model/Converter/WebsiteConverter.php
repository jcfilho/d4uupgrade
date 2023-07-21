<?php
/**
 * Created by PhpStorm.
 * User: juancarlosc
 * Date: 7/29/18
 * Time: 21:43
 */

namespace Onetree\ImportDataFixtures\Model\Converter;

/**
 * Class WebsiteConverter
 * @package Onetree\ImportDataFixtures\Model\Converter
 */
class WebsiteConverter extends AbstractConverter implements ConverterInterface
{
    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    private $websiteFactory;
    /**
     * @var \Magento\Store\Model\ResourceModel\Website
     */
    private $websiteResourceModel;
    /**
     * @var \Magento\Store\Model\WebsiteRepository
     */
    private $websiteRepository;
    /**
     * @var \Onetree\ImportDataFixtures\Logger\Logger
     */
    private $logger;

    /**
     * WebsiteConverter constructor.
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Store\Model\ResourceModel\Website $websiteResourceModel
     * @param \Magento\Store\Model\WebsiteRepository $websiteRepository
     * @param \Onetree\ImportDataFixtures\Logger\Logger $logger
     * @param array $data
     */
    public function __construct(
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Store\Model\ResourceModel\Website $websiteResourceModel,
        \Magento\Store\Model\WebsiteRepository $websiteRepository,
        \Onetree\ImportDataFixtures\Logger\Logger $logger,
        array $data = [
            self::KEY_COLUMN => 'website_id'
        ]
    )
    {
        parent::__construct($data);

        $this->websiteFactory = $websiteFactory;
        $this->websiteResourceModel = $websiteResourceModel;
        $this->websiteRepository = $websiteRepository;
        $this->logger = $logger;
    }

    /**
     * @param array $row
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function convert($row)
    {
        $value = $row[$this->getData(self::KEY_COLUMN)];
        $websitesCode = explode(',', $value);
        $websiteIds = [];
        foreach ($websitesCode as $websiteCode) {
            if (is_numeric($websiteCode)) {
                $website = $this->websiteRepository->getById($websiteCode);
            } else {
                $website = $this->websiteRepository->get($websiteCode);
            }

            $websiteIds[] = $website->getId();
        }
        if (count($websiteIds) == 1) {
            $websiteIds = $websiteIds[0];
        }
        $row[$this->getData(self::KEY_COLUMN)] = $websiteIds;

        return $row;
    }
}
