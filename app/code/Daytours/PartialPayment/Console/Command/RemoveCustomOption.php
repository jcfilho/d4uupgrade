<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Daytours\PartialPayment\Console\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RemoveCustomOption
 */
class RemoveCustomOption extends Command
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
        $this->setName('partialpayments:removeCustomOption')
            ->setDescription('Remove partial payment option to all products');
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
                $customOptions = $objectManager->get('Magento\Catalog\Model\Product\Option')->getProductOptionCollection($product);
                if ($customOptions) {
                    foreach($customOptions as $option){
                        if($option->getTitle() === "Pay Partially"){
                            $option->delete();
                            $output->writeln($product->getName() . " Partial Payment option has been deleted successfully");
                        }
                    }
                } else {
                    $output->writeln($product->getName() . " Any custom option here...");
                }
            } catch (Exception $err) {
                $output->writeln($product->getName() . " FAIL");
                $output->writeln($err->getMessage());
                exit();
            }
        }
    }
}
