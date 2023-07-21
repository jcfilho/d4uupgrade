<?php

namespace Daytours\Provider\Api\Data;

interface ProviderInterface
{
    const ENTITY_ID     = 'entity_id';
    const NAME          = 'name';
    const EMAIL         = 'email';
    const PHONE         = 'phone';

    /**
     * Get Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get Name
     *
     * @return int|null
     */
    public function getName();

    /**
     * Set Name
     *
     * @param int $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get Email
     *
     * @return string|null
     */
    public function getEmail();

    /**
     * Set Email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get Phone
     *
     * @return string|null
     */
    public function getPhone();

    /**
     * Set Email
     *
     * @param string $phone
     * @return $this
     */
    public function setPhone($phone);


}