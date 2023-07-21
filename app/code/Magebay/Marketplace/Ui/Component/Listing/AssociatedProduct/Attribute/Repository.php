<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Ui\Component\Listing\AssociatedProduct\Attribute;

class Repository extends \Magento\Catalog\Ui\Component\Listing\Attribute\AbstractRepository
{
    /** @var \Magento\Framework\App\RequestInterface */
    protected $request;

    /**
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\RequestInterface $request
    ) {
        parent::__construct($productAttributeRepository, $searchCriteriaBuilder);
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    protected function buildSearchCriteria()
    {
        return $this->searchCriteriaBuilder
            ->addFilter('attribute_code', $this->request->getParam('attributes_codes', []), 'in')
            ->create();
    }
}
