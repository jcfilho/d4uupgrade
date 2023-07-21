<?php


namespace Daytours\LastMinute\Observer\Frontend;

use Magento\Framework\App\Area as AreaAlias;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterfaceAlias;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Daytours\LastMinute\Model\Order as LastMinuteOrder;
use Daytours\LastMinute\Helper\LastMinute;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Daytours\Provider\Api\ProviderRepositoryInterface;

class SendEmailToProvider  implements ObserverInterface
{
    const EMAIL_TEMPLATE_TO_PROVIDER = 'sales_email_order_lastminute_template_provider';
    const SENDER_NAME = 'trans_email/ident_general/name';
    const SENDER_EMAIL = 'trans_email/ident_general/email';

    /**
     * @var LastMinute
     */
    private $lastMinuteHelper;
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
     * @var ProviderRepositoryInterface
     */
    private $providerRepository;

    /**
     * SendEmailToProvider constructor.
     * @param LastMinute $lastMinuteHelper
     * @param StateInterface $inlineTranslation
     * @param Escaper $escaper
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterfaceAlias $scopeConfig
     * @param ProviderRepositoryInterface $providerRepository
     */
    public function __construct(
        LastMinute $lastMinuteHelper,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        ScopeConfigInterfaceAlias $scopeConfig,
        ProviderRepositoryInterface $providerRepository
    )
    {
        $this->lastMinuteHelper = $lastMinuteHelper;
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->providerRepository = $providerRepository;
    }
    public function execute(EventObserver $observer)
    {

        $order = $observer->getEvent()->getOrder();

        if( $order->getData('is_lastminute') ){

            $items = $order->getAllVisibleItems();
            if (count($items)) {
                foreach ($items as $item) {
                    $product = $item->getProduct();

                    if( $this->lastMinuteHelper->ifHasLastminute($product) ){
                        try {

                            $providerId = $product->getBookingProvider();

                            if( $providerId && $providerId != "" && $item->getProductOptions()){
                                $this->inlineTranslation->suspend();

                                $provider = $this->providerRepository->getById($providerId);

                                $emailProvider = $provider->getEmail();
                                $nameProvider = $provider->getName();
                                $sender = $this->getSender();
                                $templateOptions = $this->getTemplateOptions($order->getStoreId());
                                $templateVars = [];

                                $productOptions = $item->getProductOptions();
                                if( $productOptions ){
                                    if( isset($productOptions['info_buyRequest']) ){

                                        $billingAddress = $order->getBillingAddress();

                                        $checkIn        = $productOptions['info_buyRequest']['check_in'];
                                        $customerName   = $billingAddress->getName();
//                                        $customerName   = $billingAddress->getName() $order->getCustomerName();
                                        $activity       = $product->getName();
                                        $meetingHour    = $product->getLastMinuteHour();
                                        $meetingPoint   = $product->getLastMinuteMeetingPoint();
                                        $qty_person     = $item->getQtyOrdered();

                                        if( is_array($productOptions) ){
                                            $infoByRequest = $productOptions['info_buyRequest'];
                                            $qty_person = $infoByRequest['qty'];

                                            if( isset($productOptions['options']) ){
                                                foreach ($productOptions['options'] as $option){
                                                    if( isset($option['label']) ){
                                                        if( preg_match('/children|niños|crianças|enfants/',strtolower($option['label'])) ){
                                                            $qty_person += (int) $option['value'];
                                                        }
                                                    }
                                                }
                                            }

                                        }

                                        $templateVars = $this->getTemplateVars($nameProvider,$checkIn,$customerName,$activity,$qty_person,$meetingHour,$meetingPoint);
                                    }
                                }

                                $transport = $this->transportBuilder
                                    ->setTemplateIdentifier(self::EMAIL_TEMPLATE_TO_PROVIDER)
                                    ->setTemplateOptions( $templateOptions )
                                    ->setTemplateVars( $templateVars )
                                    ->setFrom($sender)
                                    ->addTo($emailProvider)
                                    ->getTransport();
                                $transport->sendMessage();
                                $this->inlineTranslation->resume();
                            }


                        } catch (\Exception $e) {
                            $error = $e;
                        }

                    }

                }
            }

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

    public function getTemplateVars($nameProvider,$checkIn,$customerName,$activity,$qty_person,$meetingHour,$meetingPoint){

        return [
            'booking_date'      => $checkIn,
            'customer_name'     => $customerName,
            'qty_person'        => $qty_person,//
            'activity'          => $activity,
            'operator_name'     => $nameProvider,
            'name'              => $customerName,
            'service'           => $activity,
            'date_service'      => $checkIn,
            'hour'              => $meetingHour,
            'pax_number'        => $qty_person, //
            'meeting_point'     => $meetingPoint,
        ];

    }

}