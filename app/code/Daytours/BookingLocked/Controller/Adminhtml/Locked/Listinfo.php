<?php

namespace Daytours\BookingLocked\Controller\Adminhtml\Locked;

class Listinfo extends \Magento\Backend\App\AbstractAction
{
    protected $resultPageFactory = false;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonResultFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    )
    {
        parent::__construct($context);
        $this->jsonResultFactory = $jsonResultFactory;
    }

    public function execute()
    {

        $params = $this->getRequest()->getParams();
        $data = ['result_html' => ""];
        if (isset($params['productId'])){
            $dataToBlock = [
                'productId' => $params['productId']
            ];

            $template = 'Daytours_BookingLocked::list-locked-dates.phtml';
            if (isset($params['calendarNumber'])) {
                $template = 'Daytours_BookingLocked::list-locked-dates-calendar-two.phtml';
            }
            $resultList = $this->_view->getLayout()
                ->createBlock('Daytours\BookingLocked\Block\Adminhtml\ListInfoLocked')
                ->setData($dataToBlock)
                ->setTemplate($template)
                ->toHtml();

            $data = ['result_html' => $resultList];
        }

        $result = $this->jsonResultFactory->create();
        $result->setData($data);

        return $result;
    }

}