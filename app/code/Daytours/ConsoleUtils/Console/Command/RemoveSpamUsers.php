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
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Daytours\EditOrder\Model\Order\Email\LogFactory;


/**
 * Class generic
 */
class RemoveSpamUsers extends Command
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    protected $_customer;
    protected $_customerFactory;


    public function __construct(
        // \Magento\Framework\App\State $state,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Customer $customers,
        \Magento\Framework\Registry $registry
    ) {
        // $this->state = $state;
        // $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND); //NO QUITAR!!
        $this->_customerFactory = $customerFactory;
        $this->_customer = $customers;
        $this->registry = $registry;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('utils:removeSpamUsers')
            ->setDescription('Remove Spam Users by Name');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->registry->register('isSecureArea', true);
        $customerCollection = $this->getCustomerCollection();
        $count = 0;
        foreach ($customerCollection as $customer) {
            if (strpos($customer->getName(), "www")) {
                $count++;
                echo "#$count  USER: " . $customer->getId() . " NAME: " . $customer->getName() . "\n";
                $customer->delete();
            }
        }
    }

    public function getCustomerCollection()
    {
        return $this->_customer->getCollection()
            ->addAttributeToSelect("*")
            ->load();
    }
}

