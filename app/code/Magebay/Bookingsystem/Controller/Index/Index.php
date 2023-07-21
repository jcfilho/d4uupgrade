<?php
 
namespace Magebay\Bookingsystem\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magebay\Bookingsystem\Helper\Data as BkHelper;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_bkHelper;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
    */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
		BkHelper $bkHelper
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_bkHelper = $bkHelper;
    }
    public function execute()
    {
		$enableSearch = $this->_bkHelper->getFieldSetting('bookingsystem/search_setting/enable_search');
		$enable = $this->_bkHelper->getFieldSetting('bookingsystem/setting/enable');
		if($enable == 1 && $enableSearch == 1)
		{
			 $resultPageFactory = $this->resultPageFactory->create();
 
			// Add page title
			$resultPageFactory->getConfig()->getTitle()->set(__('Find Your Booking'));
	 
			// Add breadcrumb
		   /** @var \Magento\Theme\Block\Html\Breadcrumbs */
		   if($resultPageFactory->getLayout()->getBlock('breadcrumbs'))
		   {
			    $breadcrumbs = $resultPageFactory->getLayout()->getBlock('breadcrumbs');
				$breadcrumbs->addCrumb('home',
					[
						'label' => __('Home'),
						'title' => __('Home'),
						'link' => $this->_url->getUrl('')
					]
				);
				$breadcrumbs->addCrumb('booking_search',
					[
						'label' => __('Booking Search'),
						'title' => __('Booking Search')
					]
				);
		   }
			return $resultPageFactory;
		}
    }
}
 