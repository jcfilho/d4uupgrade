<?php

namespace Daytours\Bookingsystem\Block;

use Magento\Backend\Block\Template;

class Transfer extends Template
{
    const CALENDAR_NUMBER_BY_DEFAULT = 1;
    const CALENDAR_NUMBER_BY_SECOND = 2;
    const ATTRIBUTE_SET_TRANSFER_NAME = 'Transfer';

    function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);

    }

    function getAttributeSetByTransfer(){
        return self::ATTRIBUTE_SET_TRANSFER_NAME;
    }
}