<?php
/**
 * Venustheme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Venustheme
 * @package    Ves_Megamenu
 * @copyright  Copyright (c) 2016 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

namespace Ves\Megamenu\Block;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\Context;

class MobileMenu extends \Magento\Framework\View\Element\Template implements
\Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var \Ves\Megamenu\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Ves\Megamenu\Model\Menu
     */
    protected $_menu;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Json Serializer Instance
     *
     * @var Json
     */
    private $json;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Ves\Megamenu\Helper\Data                        $helper
     * @param \Magento\Framework\ObjectManagerInterface        $objectManager
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param Json|null                                        $json
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ves\Megamenu\Helper\Data $helper,
        \Ves\Megamenu\Model\Menu $menu,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
        Json $json = null,
        array $data = []
        )
    {
        parent::__construct($context, $data);
        $this->_helper          = $helper;
        $this->_menu            = $menu;
        $this->_objectManager   = $objectManager;
        $this->_customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->json = $json ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->addData(
            [
                'cache_lifetime' => false,
                'cache_tags' => [\Ves\Megamenu\Model\Menu::CACHE_MOBILE_TAG, \Magento\Store\Model\Group::CACHE_TAG],
            ]
        );
    }

    /**
     * Get customer group id
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        if(!isset($this->_customer_group_id)) {
            $this->_customer_group_id = (int)$this->_customerSession->getCustomerGroupId();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $context = $objectManager->get('Magento\Framework\App\Http\Context');
            $isLoggedIn = $context->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
            if(!$isLoggedIn) {
               $this->_customer_group_id = 0;
            }
        }
        return $this->_customer_group_id;
    }

    /**
     * get megamenu key
     *
     * @param string $megamenu_key
     * @return string
     */
    public function getMegamenuKey($megamenu_key)
    {
        $params = $this->getRequest()->getParams();
        $params_key = "";
        if ($params) {
            $params_key = array_values($params);
            $params_key = $params_key ? implode('|', $params_key) : "";
            $params_key = $params_key ? ("-". md5($params_key) ) : "";
        }
        return $megamenu_key.$params_key;
    }

    /**
     * Get key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $menu = $this->getData('menu');
        $menuId = $this->getData('id');
        $menuId = $menuId?$menuId:0;
        $code = $this->getConfig('alias');
        if ($menu) {
            $menuId = $menu->getMenuId();
            $code = $menu->getAlias();
        }

        $custom_menu_class = $this->getConfig('custom_class');
        $custom_menu_class = $custom_menu_class?("-".$custom_menu_class):'';

        $megamenu_key = $code."-".$menuId.$custom_menu_class;
        $customerGroupId = (int)$this->getCustomerGroupId();
        $customerGroupId = $customerGroupId?("group".$customerGroupId):"group0";
        $megamenu_key .= "-".$customerGroupId;
        $megamenu_key .= "-mobilemenu";

        $shortCacheId = [
            'VES_MEGAMENU_MOBILE_MENU_BLOCK',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(Context::CONTEXT_GROUP),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            $this->getMegamenuKey($megamenu_key)
            //,$this->json->serialize($this->getRequest()->getParams())
        ];
        $cacheId = $shortCacheId;

        $shortCacheId = array_values($shortCacheId);
        $shortCacheId = implode('|', $shortCacheId);
        $shortCacheId = md5($shortCacheId);

        $cacheId['megamenu_mobile_key'] = $this->getMegamenuKey($megamenu_key);
        $cacheId['short_cache_id'] = $shortCacheId;

        return $cacheId;
    }

    /**
     * @inheritdoc
     */
    public function _toHtml()
    {
        if(!$this->getTemplate()){
            $this->setTemplate("Ves_Megamenu::mobile_menu.phtml");
        }
        $store = $this->_storeManager->getStore();
        $html = $menu = '';
        $menu = $this->getData('menu');
        $alias = $this->getData('alias');
        $menu_id = $this->getData('id');

        if (!$menu) {
            $menu = $this->getMenuProfile($menu_id, $alias);
        }

        if ($menu) {
            $customerGroups = $menu->getData('customer_group_ids');
            $customerGroupId = (int)$this->getCustomerGroupId();
            if ($customerGroupId) {
                if(!in_array($customerGroupId, $customerGroups)) return;
            }
            $this->setData("menu", $menu);
        }

        return parent::_toHtml();
    }

    /**
     * Get menu profile
     *
     * @param int $menuId
     * @param string $alias
     * @return mixed|bool
     */
    public function getMenuProfile($menuId = 0, $alias = "")
    {
        $menu = false;
        $store = $this->_storeManager->getStore();
        if ($menuId) {
            $menu = $this->_menu->setStore($store)->load((int)$menuId);
            if ($menu->getId() != $menuId) {
                $menu = false;
            }
        } elseif ($alias) {
            $menu = $this->_menu->setStore($store)->load(addslashes($alias));
            if ($menu->getAlias() != $alias) {
                $menu = false;
            }
        }
        if ($menu && !$menu->getStatus()) {
            $menu = false;
        }
        return $menu;
    }

    /**
     * Get config
     * @param string $key
     * @param mixed|string|null $default
     * @return mixed|string|null
     */
    public function getConfig($key, $default = NULL)
    {
        if ($this->hasData($key)) {
            return $this->getData($key);
        }
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        return [\Ves\Megamenu\Model\Menu::CACHE_MOBILE_TAG, \Magento\Store\Model\Group::CACHE_TAG];
    }
}
