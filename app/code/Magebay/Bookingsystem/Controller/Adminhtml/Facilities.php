<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml;
 
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magebay\Bookingsystem\Model\FacilitiesFactory;
use Magebay\Bookingsystem\Model\Image as ImageModel;
use Magebay\Bookingsystem\Model\Upload as UploadImages;
use Magebay\Bookingsystem\Helper\BkText as BkHelperText;
class Facilities extends Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
	/**
     * Image model
     *
     * @var  Magebay\Bookingsystem\Model\Image;
     */
	protected $_imageModel;
	/**
     * Image images
     *
     * @var  Magebay\Bookingsystem\Model\Upload;
     */
	protected $_uploadImages;
    /**
     * Result page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;
 
    /**
     * Facilities model factory
     *
     * @var \Magebay\Bookingsystem\Model\FacilitiesFactory
     */
    protected $_facilitiesFactory;
	/**
	* booking Helper
	**/
	protected $_bkHelperText;
    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param FacilitiesFactory $newsFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        FacilitiesFactory $facilitiesFactory,
		ImageModel $ImageModel,
		UploadImages $uploadImages,
		BkHelperText $bkHelperText
    ) {
       parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_facilitiesFactory = $facilitiesFactory;
		$this->_imageModel = $ImageModel;
		$this->_uploadImages = $uploadImages;
		$this->_bkHelperText = $bkHelperText;
    }
	public function execute()
	{
	   
	}
 
    /**
     * News access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebay_Bookingsystem::manage_facilities');
    }
}