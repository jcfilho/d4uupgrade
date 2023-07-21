<?php


namespace Daytours\BookingLocked\Controller\Adminhtml\Locked;

use Daytours\BookingLocked\Api\Data\BookingLockedInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Daytours\BookingLocked\Api\BookingLockedRepositoryInterface;
use Daytours\BookingLocked\Api\Data\BookingLockedInterfaceFactory;
use Magento\Framework\Controller\Result\JsonFactory as JsonFactoryAlias;

class Save extends AbstractAction
{
    protected $resultPageFactory = false;
    /**
     * @var JsonFactoryAlias
     */
    private $jsonResultFactory;
    /**
     * @var BookingLockedInterfaceFactory
     */
    private $bookingLockedInterfaceFactory;
    /**
     * @var BookingLockedRepositoryInterface
     */
    private $bookingLockedRepository;

    public function __construct(
        Context $context,
        JsonFactory $jsonResultFactory,
        BookingLockedInterfaceFactory $bookingLockedInterfaceFactory,
        BookingLockedRepositoryInterface $bookingLockedRepository
    )
    {
        parent::__construct($context);
        $this->jsonResultFactory = $jsonResultFactory;
        $this->bookingLockedInterfaceFactory = $bookingLockedInterfaceFactory;
        $this->bookingLockedRepository = $bookingLockedRepository;
    }

    public function execute()
    {

        $params = $this->getRequest()->getParams();
        $data = ['result' => false];
        if (isset($params['productId']) && isset($params['date'])){

            $calendarNumber = BookingLockedInterface::CALENDAR_ONE;
            if (isset($params['calendarNumber'])) {
                $calendarNumber = BookingLockedInterface::CALENDAR_TWO;
            }

            if($this->bookingLockedRepository->lockedDateExist($params['productId'],$params['date'],$calendarNumber)){
                /** @var BookingLockedInterface $locked */
                $locked = $this->bookingLockedInterfaceFactory->create();
                $locked->setLocked($params['date']);
                $locked->setProductId($params['productId']);
                $locked->setCalendarNumber($calendarNumber);

                if( $this->bookingLockedRepository->save($locked) ){
                    $data['result'] = true;
                    $data['message'] = __('Locked date saved successfully.');
                }else{
                    $data['message'] = __('Error saving locked date, please try again.');
                }
            }else{
                $data['message'] = __('Date selected already exist, please check your list and try again.');
            }

            $result = $this->jsonResultFactory->create();
            $result->setData($data);

            return $result;

        }



        $result = $this->jsonResultFactory->create();
        $result->setData($data);

        return $result;
    }
}