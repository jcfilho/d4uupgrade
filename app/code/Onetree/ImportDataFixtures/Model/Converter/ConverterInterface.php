<?php
/**
 * Created by PhpStorm.
 * User: juancarlosc
 * Date: 7/29/18
 * Time: 21:43
 */

namespace Onetree\ImportDataFixtures\Model\Converter;

/**
 * Interface ConverterInterface
 * @package Onetree\ImportDataFixtures\Model\Converter
 */
interface ConverterInterface
{
    /**
     * @param $row array
     * @return array
     */
    public function convert($row);
}