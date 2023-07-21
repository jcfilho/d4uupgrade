<?php

namespace Daytours\ErrorLogs\Block;


use Daytours\ErrorLogs\Model\ErrorLogFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ErrorLogs extends \Magento\Framework\View\Element\Template
{

    /**
     * @var ErrorLogFactory
     */
    protected $dataFactory;

    protected $token;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        ScopeConfigInterface $scopeConfig,
        ErrorLogFactory $dataFactory
    ) {
        parent::__construct($context);
        $this->dataFactory = $dataFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function getErrorList(){
        return $this->dataFactory->create()->getCollection()->getData();
    }

}
