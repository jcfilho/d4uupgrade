<?php
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Controller\Product\Configurable;

class GetAttributes extends \Magebay\Marketplace\Controller\Product\Account
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $customerSession);
    }

    /**
     * Get attributes
     *
     * @return void
     */
    public function execute()
    {
        $attributes = $this->_objectManager->get(\Magento\ConfigurableProduct\Model\AttributesList::class)->getAttributes($this->getRequest()->getParam('attributes'));
        $data = $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($attributes);
		$this->getResponse()->representJson($data);
    }
}