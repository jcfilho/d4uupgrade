<?php

namespace Daytours\ErrorLogs\Api\Data;

interface ErrorLogInterface
{
    /**
     * @return string
     */
    public function getModuleName();

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @return int
     */
    public function getDate();

}