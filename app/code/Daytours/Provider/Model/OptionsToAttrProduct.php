<?php


namespace Daytours\Provider\Model;

use \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class OptionsToAttrProduct extends AbstractSource
{
    /**
     * @var ResourceModel\Provider\CollectionFactory
     */
    private $providerCollectionFactory;

    /**
     * OptionsToAttrProduct constructor.
     * @param ResourceModel\Provider\CollectionFactory $providerCollectionFactory
     */
    public function __construct(
        \Daytours\Provider\Model\ResourceModel\Provider\CollectionFactory $providerCollectionFactory
    )
    {
        $this->providerCollectionFactory = $providerCollectionFactory;
    }

    public function getAllOptions()
    {

        $providers = [];
        $providers[] = [
            'value' => '',
            'label' => __('Select...')
        ];

        foreach ($this->providerCollectionFactory->create() as $provider) {
            $providers[] = [
                'value' => $provider->getId(),
                'label' => $provider->getName()
            ];
        }

        $this->_options = $providers;

        return $this->_options;
    }
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}