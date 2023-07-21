<?php

namespace Daytours\Provider\Block\Adminhtml\Provider\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Cms\Api\BlockRepositoryInterface;
use Daytours\Provider\Api\ProviderRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ProviderRepositoryInterface
     */
    protected $providerRepository;

    /**
     * @param Context $context
     * @param ProviderRepositoryInterface $providerRepository
     */
    public function __construct(
        Context $context,
        ProviderRepositoryInterface $providerRepository
    ) {
        $this->context = $context;
        $this->providerRepository = $providerRepository;
    }

    /**
     * Return CMS block ID
     *
     * @return int|null
     */
    public function getProviderId()
    {
        try {
            return $this->providerRepository->getById(
                $this->context->getRequest()->getParam('entity_id')
            )->getId();
        } catch (NoSuchEntityException $e) {
        }
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
