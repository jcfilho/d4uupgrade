<?php

namespace Magebay\Marketplace\Controller\Product\Bundle;

class Grid extends \Magebay\Marketplace\Controller\Product\Account
{
    /**
     * @return mixed
     */
    public function execute()
    {
        $index = $this->getRequest()->getParam('index');
        if (!preg_match('/^[a-z0-9_.]*$/i', $index)) {
            throw new \InvalidArgumentException('Invalid parameter "index"');
        }

        return $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(
                'Magebay\Marketplace\Block\Product\Bundle\Search\Grid',
                'marketplace_product_bundle_option_search_grid'
            )->setIndex($index)->toHtml()
        );
    }
}
