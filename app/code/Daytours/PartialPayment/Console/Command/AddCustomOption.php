<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Daytours\PartialPayment\Console\Command;

use Exception;
use PhpParser\Node\Stmt\TryCatch;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AddCustomOption
 */
class AddCustomOption extends Command
{
    /** @var \Magento\Framework\App\State **/
    private $state;

    protected $_productCollectionFactory;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\App\State $state
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->state = $state;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('partialpayments:addCustomOption')
            ->setDescription('Add partial payment option to all products (WARNING)');

        parent::configure();
    }

    public function getProductCollection()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        $productCollection = $this->getProductCollection();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        foreach ($productCollection as $product) {
            try {
                
                //CREATE CUSTOM OPTION
                $customOption = $objectManager->create('Magento\Catalog\Api\Data\ProductCustomOptionInterface');
                $customOption->setTitle('Pay Partially')
                ->setType('checkbox')
                ->setIsRequire(false)
                ->setSortOrder(1)
                ->setPrice(-80.00)
                ->setPriceType('percent')
                ->setMaxCharacters(50)
                ->setProductSku($product->getSku());
                
                //CREATE OPTION VALUE
                $optionValue = $objectManager->create('Magento\Catalog\Model\Product\Option\Value');
                $optionValue->setSortOrder(0);
                $optionValue->setTitle("20%");
                $optionValue->setPrice(-80.00);
                $optionValue->setPriceType("percent");
                $optionValue->setSku($product->getSku());
                $customOption->addValue($optionValue);
                
                //ADD CUSTOM OPTION TO PRODUCT
                $product->addOption($customOption);
                $product->save();
                $output->writeln($product->getName() . " OK");

            } catch (Exception $err) {
                $output->writeln($product->getName() . " FAIL");
                $output->writeln($err->getMessage());
                exit();
            }
        }
    }
}
