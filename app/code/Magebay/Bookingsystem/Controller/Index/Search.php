<?php

namespace Magebay\Bookingsystem\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magebay\Bookingsystem\Helper\BkHelperDate;

class Search extends \Magento\Framework\App\Action\Action
{
    /**
     * Result page factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory;
     */
    protected $_resultJsonFactory;
    protected $_bkHelperDate;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        BkHelperDate $bkHelperDate
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_bkHelperDate = $bkHelperDate;
    }
    public function execute()
    {
        $resultJson = $this->_resultJsonFactory->create();
        $isViewPap = $this->getRequest()->getParam('is_view_map',0);
        // $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Search')->getBkDataBookings();
        $htmlItems = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Search')->setTemplate('Magebay_Bookingsystem::search/result-search.phtml')->toHtml();
        $htmlSidebar = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Search')->setTemplate('Magebay_Bookingsystem::search/sidebar.phtml')->toHtml();
        $htmlMap = '';
        $isMap = $this->_bkHelperDate->getFieldSetting('bookingsystem/search_setting/use_map_search');
        if($isMap == 1 && $this->checkEnterPriseVersion() && $isViewPap == 1)
        {
            $htmlMap = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Search')->setTemplate('Magebay_Bookingsystem::search/map.phtml')->toHtml();
        }
        $response = array('html_items'=> $htmlItems,'html_sidebar'=>$htmlSidebar,'html_map'=>$htmlMap);
        return $resultJson->setData($response);
    }
    private function checkEnterPriseVersion()
    {
        $mapFile = BP.'/app/design/frontend/Magebay/bookingtheme/Magebay_Bookingsystem/templates/search/map.phtml';
        $ok = false;
        if(file_exists($mapFile))
        {
            $ok = true;
        }
        return $ok;
    }
}
