<?php

namespace Onetree\ImportDataFixtures\Model\Fixture;

interface FixtureInterface
{
    /**
     * @param array $fixtures
     * @return void
     */
    public function install(array $fixtures);

    /**
     * @param \Onetree\ImportDataFixtures\Model\Converter\ConverterInterface $converter
     * @return void
     */
    public function addConverter($converter);
}