<?php
/**
 * Created by PhpStorm.
 * User: juancarlosc
 * Date: 8/6/18
 * Time: 00:30
 */

namespace Onetree\ImportDataFixtures\Model\Converter;

/**
 * Class AbstractConverter
 * @package Onetree\ImportDataFixtures\Model\Converter
 */
abstract class AbstractConverter extends \Magento\Framework\DataObject implements ConverterInterface
{
    const KEY_COLUMN = 'column';

    const KEY_CURRENT_MODULE = 'current_module';

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}