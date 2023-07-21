<?php

namespace Daytours\PartialPayment\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultRedirect;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\ResultFactory $result
    ) {
        parent::__construct($context);
        $this->resultRedirect = $result;
    }

    /**
     * @return string
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
    }
}
