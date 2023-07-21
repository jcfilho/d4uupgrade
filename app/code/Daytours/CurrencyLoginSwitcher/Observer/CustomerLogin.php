<?php

namespace Daytours\CurrencyLoginSwitcher\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerLogin implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }


    /**
     * Retrieve cookie manager
     *
     * @deprecated
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {

        $publicCookieMetadata = $this->getCookieMetadataFactory()->createPublicCookieMetadata();
        $publicCookieMetadata->setDurationOneYear();
        $publicCookieMetadata->setPath('/');
        $publicCookieMetadata->setHttpOnly(false);

        $currencySupported = array("ARS", "BRL", "GBP", "CAD", "CLP", "COP", "EUR", "USD","MXN","PEN","INR","ILS","JPY");
        $user_currency = $this->getCookieManager()->getCookie('user_currency');

        if (in_array($user_currency, $currencySupported)) {
            $currency = $user_currency;
            if ($this->getStoreCurrency() !== $currency) {
                $this->storeManager->getStore()->setCurrentCurrencyCode($currency);
            }
        }

        $this->getCookieManager()->deleteCookie('user_currency', $publicCookieMetadata);

    }

    private function getStoreCurrency()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }

    // /**
    //  * @param string $url ATENCION! Usar http incluso se la peticion usa https
    //  * @param string $method Todo en mayuscula
    //  * @param mixed $data
    //  * @return string
    //  */
    // private function enviarPeticion($url, $method, $data)
    // {
    //     // use key 'http' even if you send the request to https://...
    //     $options = array(
    //         'http' => array(
    //             'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
    //             'method'  => $method,
    //             'content' => http_build_query($data)
    //         )
    //     );
    //     $context  = stream_context_create($options);
    //     $result = file_get_contents($url, false, $context);
    //     if ($result === FALSE) { /* Handle error */
    //         return false;
    //     }

    //     return $result;
    // }
}
