<?php

namespace Daytours\EditOrder\Model\Order\Email;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Sales\Model\Order;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Daytours\EditOrder\Helper\Data as DataEditOrder;


class Sender
{
    const MISSING_DATA_TEMPLATE = 'daytours_editorder_missing_data_template';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var DataEditOrder
     */
    private $dataEditOrder;


    /**
     * @param StoreManagerInterface $storeManager
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlInterface,
        DataEditOrder $dataEditOrder
    )
    {
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->_urlInterface = $urlInterface;
        $this->dataEditOrder = $dataEditOrder;
    }

    /**
     * Sends missing data order email to the customer.
     *
     * @param Order $order
     */
    public function send(Order $order, $countdown)
    {

        $templateOptions = array(
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $order->getStoreId()
//            'store' => $this->storeManager->getStore()->getId()
        );
        $templateVars = array(
            'store' => $this->storeManager->getStore(),
            'edit_url' => $this->dataEditOrder->getURLEncrypted($order->getId() , $order->getCustomerEmail(),$order->getStoreId()),
            'order' => $order,
            'countdown' => $countdown
        );
        $from = array(
            'email' => $this->scopeConfig->getValue('trans_email/ident_sales/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'name' => $this->scopeConfig->getValue('trans_email/ident_sales/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
        );
        $this->inlineTranslation->suspend();
        $to = array($order->getCustomerEmail());
        $transport = $this->transportBuilder->setTemplateIdentifier(self::MISSING_DATA_TEMPLATE)
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFrom($from)
            ->addTo($to)
            ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }
}
