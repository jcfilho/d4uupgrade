<?php
 /** 
 ** Code for version 2.1 or more 
 */
namespace Magebay\Bookingsystem\Controller\Adminhtml\Booking;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
class ChangeBookingType extends Action
{
	 

	protected $_resultJsonFactory;
	 
	function __construct
	(
		Context $context,
		JsonFactory $resultJsonFactory
	)
	{
		parent::__construct($context);
		$this->_resultJsonFactory = $resultJsonFactory;
	}
	public function execute()
	{
		$status = 'error';
		$message = 'System error. You can not change product type';
		$bookingId = $this->getRequest()->getParam('booking_id',0);
		if($bookingId > 0)
		{
			$_product = $this->_objectManager->get('Magento\Catalog\Model\Product');
			try{
				$_product->load($bookingId)->setTypeId('booking')->save();
				$status = 'success';
				$message = 'You have been change product type';
			}
			catch(Exception $e)
			{
				$message = $e->getMessage();
			}
		}
		$resultJson = $this->_resultJsonFactory->create();
		$response = array('status'=> $status,'message'=>$message);
		return $resultJson->setData($response);
	}
	
}