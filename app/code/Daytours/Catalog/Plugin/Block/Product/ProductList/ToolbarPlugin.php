<?php

namespace Daytours\Catalog\Plugin\Block\Product\ProductList;

class ToolbarPlugin
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    public function __construct(\Magento\Framework\App\Request\Http $request)
    {
        $this->request = $request;
    }

    public function afterGetAvailableOrders(\Magento\Catalog\Block\Product\ProductList\Toolbar $subject, $result)
    {
        if (
            $this->request->getRouteName() == 'catalogsearch'
            && $this->request->getControllerName() == 'result'
        ) {

//            if (isset($result['position'])) {
//                unset($result['position']);
//            }
//            if (isset($result['relevance'])) {
//                unset($result['relevance']);
//            }
        }

        return $result;
    }
}