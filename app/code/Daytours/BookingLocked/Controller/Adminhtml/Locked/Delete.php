<?php


namespace Daytours\BookingLocked\Controller\Adminhtml\Locked;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Daytours\BookingLocked\Api\BookingLockedRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory as JsonFactoryAlias;

class Delete extends AbstractAction
{
    protected $resultPageFactory = false;
    /**
     * @var JsonFactoryAlias
     */
    private $jsonResultFactory;
    /**
     * @var BookingLockedRepositoryInterface
     */
    private $bookingLockedRepository;

    public function __construct(
        Context $context,
        JsonFactory $jsonResultFactory,
        BookingLockedRepositoryInterface $bookingLockedRepository
    )
    {
        parent::__construct($context);
        $this->jsonResultFactory = $jsonResultFactory;
        $this->bookingLockedRepository = $bookingLockedRepository;
    }

    public function execute()
    {

        $params = $this->getRequest()->getParams();
        $data = ['result' => false];
        if (isset($params['lockedId'])){

            if($this->bookingLockedRepository->deleteById($params['lockedId'])){
                $data['result'] = true;
                $data['message'] = __('Locked date removed successfully.');
            }else{
                $data['message'] = __('There is an error removing this item, please try again.');
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