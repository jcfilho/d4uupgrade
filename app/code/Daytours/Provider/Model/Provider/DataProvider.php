<?php


namespace Daytours\Provider\Model\Provider;

use Daytours\Provider\Model\ResourceModel\Provider\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * @var \Daytours\Provider\Model\ResourceModel\Provider\Collection
     */
    protected $collection;
    /**
     * @var array
     */
    protected $loadedData;
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    public function __construct(
        CollectionFactory $providerCollectionFactory,
        DataPersistorInterface $dataPersistor,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = [])
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $providerCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
    }

    public function getData()
    {

        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var \Daytours\Provider\Model\Provider $provider */
        foreach ($items as $provider) {
            $this->loadedData[$provider->getId()] = $provider->getData();
        }

        $data = $this->dataPersistor->get('provider');
        if (!empty($data)) {
            $provider = $this->collection->getNewEmptyItem();
            $provider->setData($data);
            $this->loadedData[$provider->getId()] = $provider->getData();
            $this->dataPersistor->clear('provider');
        }

        return $this->loadedData;

    }
}