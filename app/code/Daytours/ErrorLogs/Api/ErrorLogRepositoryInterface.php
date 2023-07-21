<?php

use \Daytours\ErrorLogs\Api\Data\ErrorLogInterface;

namespace Daytours\ErrorLogs\Api;

interface ErrorLogRepositoryInterface
{
    /**
     * @return ErrorLogInterface[]
     */
    public function getList();

    /**
     * @param mixed $data
     * @return void
     */
    public function recordError($data);
}
