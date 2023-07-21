<?php

namespace Daytours\Sales\Helper;

use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    public function __construct(Context $context, \Magento\Framework\App\Request\Http $request)
    {
        $this->request = $request;
        parent::__construct($context);
    }

    /**
     * Get the expected column span for printable order
     *
     * @return string
     */
    public function getPrintableColSpan()
    {
        return $this->isPrintOrder() ? 'colspan="4"' : '';
    }

    /**
     * Determine if the current action is the print order
     *
     * @return bool
     */
    public function isPrintOrder()
    {
        return $this->request->getRouteName() == 'sales'
            && $this->request->getControllerName() == 'order'
            && $this->request->getActionName() == 'print';
    }
}