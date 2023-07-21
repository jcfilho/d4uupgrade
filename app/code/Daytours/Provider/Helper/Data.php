<?php


namespace Daytours\Provider\Helper;

use Daytours\Provider\Model\ProviderRepository;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var ProviderRepository
     */
    private $providerRepository;

    /**
     * Data constructor.
     * @param ProductRepository $productRepository
     * @param ProviderRepository $providerRepository
     * @param Context $context
     */
    public function __construct(
        ProductRepository $productRepository,
        ProviderRepository $providerRepository,
        Context $context
    )
    {

        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->providerRepository = $providerRepository;
    }

    public function getProviderPhoneByProductId($productId){
        $product = $this->productRepository->getById($productId);
        if( $product ){
            $productProviderId = $product->getBookingProvider();
            if( $productProviderId  && $productProviderId != ''){
                $provider = $this->providerRepository->getById($productProviderId);
                return $provider->getPhone();
            }
        }
        return '';
    }
}