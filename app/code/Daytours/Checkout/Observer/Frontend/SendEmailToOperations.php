<?php


namespace Daytours\Checkout\Observer\Frontend;

use Magento\Framework\App\Area as AreaAlias;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterfaceAlias;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\WebsiteRepository;
use Magento\Directory\Model\CountryFactory;

class SendEmailToOperations  implements ObserverInterface
{
    const EMAIL_TEMPLATE_TO_PROVIDER    = 'sales_email_order_operations';
    const SENDER_NAME                   = 'trans_email/ident_general/name';
    const SENDER_EMAIL                  = 'trans_email/ident_general/email';
    const OPERATION_EMAIL               = 'trans_email/operation_email/email';
    const OPERATION_NAME                = 'trans_email/operation_email/name';

    /**
     * @var StateInterface
     */
    private $inlineTranslation;
    /**
     * @var Escaper
     */
    private $escaper;
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;
    /**
     * @var ScopeConfigInterfaceAlias
     */
    private $scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var WebsiteRepository
     */
    private $websiteRepository;
    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * SendEmailToProvider constructor.
     * @param StoreManagerInterface $storeManager
     * @param WebsiteRepository $websiteRepository
     * @param CountryFactory $countryFactory
     * @param StateInterface $inlineTranslation
     * @param Escaper $escaper
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterfaceAlias $scopeConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        WebsiteRepository $websiteRepository,
        CountryFactory $countryFactory,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        ScopeConfigInterfaceAlias $scopeConfig
    )
    {
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->websiteRepository = $websiteRepository;
        $this->countryFactory = $countryFactory;
    }
    public function execute(EventObserver $observer)
    {

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        $store = $this->storeManager->getStore($order->getStoreId());
        $websiteId = $store->getWebsiteId();
        $website = $this->websiteRepository->getById($websiteId);
        $countryCode = $order->getBillingAddress()->getData('country_id');
        $country = $this->countryFactory->create()->loadByCode($countryCode);

        $variables = [];

        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();

        $variables['order'] = $order;
        $variables['website_name'] = $website->getName(); //1
        $variables['order_number'] = $order->getIncrementId();
        $variables['order_entity_id'] = $order->getEntityId();
        $variables['client_name'] = $order->getBillingAddress()->getData('firstname') . ' ' . $order->getBillingAddress()->getData('lastname');
        $variables['client_email'] = $order->getBillingAddress()->getData('email');
        $variables['client_phone'] = $order->getBillingAddress()->getData('telephone');
        $variables['client_country'] = $country->getName();
        $variables['client_payment_method'] = $method->getTitle();

        $variables['product_sku'] = '0'; //US
        $variables['product_name'] = ''; //US
        $variables['product_options'] = ''; //US

        $emailOperator = $this->escaper->escapeHtml($this->scopeConfig->getValue(self::OPERATION_EMAIL));
        $nameOperator = $this->escaper->escapeHtml($this->scopeConfig->getValue(self::OPERATION_NAME));
        $sender = $this->getSender();

        try {
            $templateOptions = $this->getTemplateOptions($order->getStoreId());
            $transport = $this->transportBuilder
                ->setTemplateIdentifier(self::EMAIL_TEMPLATE_TO_PROVIDER)
                ->setTemplateOptions( $templateOptions )
                ->setTemplateVars( $variables )
                ->setFrom($sender)
                ->addTo($emailOperator)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $error = $e;
        }
    }

    public function getSender(){
        return [
            'name' => $this->escaper->escapeHtml($this->scopeConfig->getValue(self::SENDER_NAME)),
            'email' => $this->escaper->escapeHtml($this->scopeConfig->getValue(self::SENDER_EMAIL)),
        ];
    }

    public function getTemplateOptions($storeId){
        return [
            'area' => AreaAlias::AREA_FRONTEND,
            'store' => $storeId
        ];
    }


}