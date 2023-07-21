<?php
/**
 * Created by PhpStorm.
 * User: juancarlosc
 * Date: 7/29/18
 * Time: 12:40
 */

namespace Onetree\ImportDataFixtures\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/import_data_fixture.log';
}