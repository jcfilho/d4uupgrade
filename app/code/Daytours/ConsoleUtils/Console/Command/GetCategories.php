<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Daytours\ConsoleUtils\Console\Command;

//use Daytours\Bookingsystem\Model\Calendars;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magebay\Bookingsystem\Model\CalendarsFactory;

/**
 * Class GetCategories
 */
class GetCategories extends Command
{
    /** @var \Magento\Framework\App\State **/
    private $state;

    /** @var  \Magento\Store\Model\StoreManagerInterface **/
    private $_storeManager;

    private $calendarsFactory;

    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CalendarsFactory $calendarsFactory
    )
    {
        $this->state = $state;
        $this->_storeManager = $storeManager;
        $this->calendarsFactory = $calendarsFactory;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('utils:categories')
            ->setDescription('Obtener urls de las categorias');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $model = $this->calendarsFactory->create();
        $collection = $model->getBkCalendars();
        $collection->addFieldToFilter('calendar_booking_id',"857");
        foreach($collection as $item){
            $item->setData("calendar_price",555);
            $item->save();
            echo "Item: #".$item->getData("calendar_booking_id")." OK \n";
        }
        //var_dump($collection->toArray());
        // $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        // $objectManager = $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        // $categories = $categoryFactory->create()                              
        //     ->addAttributeToSelect('*')
        //     ->setStore($this->_storeManager->getStore())
        //     ->addAttributeToFilter('is_active','1');

        // foreach ($categories as $category) {
        //     $cantidadDeEspacios = 40-strlen($category->getName());
        //     $espacios = "";
        //     for($i=0;$i<$cantidadDeEspacios;$i++){
        //         $espacios .= " ";
        //     }
        //     $output->writeln($category->getName().$espacios." | ".$category->getUrl());
        // }
    }
}
