<?php

namespace Daytours\Bookingsystem\Controller\Adminhtml\Booking;

class GetFoomBk extends \Magebay\Bookingsystem\Controller\Adminhtml\Booking\GetFoomBk {

    public function execute()
    {
        $resultJson = $this->_resultJsonFactory->create();
        $htmlForm = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Adminhtml\Product\Edit\From')->setTemplate('Daytours_Bookingsystem::catalog/product/bk21/bk_form_content.phtml')->toHtml();
        $response = array('html_from'=> $htmlForm);
        return $resultJson->setData($response);
    }

}