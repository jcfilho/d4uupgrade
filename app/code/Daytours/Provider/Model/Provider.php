<?php

    namespace Daytours\Provider\Model;

use Daytours\Provider\Api\Data\ProviderInterface;
use Daytours\Provider\Model\ResourceModel\Provider as ProviderAlias;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * @method ProviderAlias getResource()
 * @method \Daytours\Provider\Model\ResourceModel\Provider\Collection getCollection()
 */
class Provider extends AbstractModel implements ProviderInterface, IdentityInterface
{
    const TABLE_NAME        = 'booking_provider';
    const CACHE_TAG         = 'daytours_provider_provider';
    protected $_cacheTag    = 'daytours_provider_provider';
    protected $_eventPrefix = 'daytours_provider_provider';

    protected function _construct()
    {
        $this->_init(ProviderAlias::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }
    public function getName()
    {
        return $this->getData(self::NAME);
    }
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }
    public function getPhone()
    {
        return $this->getData(self::PHONE);
    }
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID,$id);
    }
    public function setName($name)
    {
        return $this->setData(self::NAME,$name);
    }
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL,$email);
    }

    public function setPhone($phone)
    {
        return $this->setData(self::PHONE,$phone);
    }

}