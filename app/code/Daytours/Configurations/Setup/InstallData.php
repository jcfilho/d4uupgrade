<?php

namespace Daytours\Configurations\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Framework\Registry;

use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\ResourceModel\Store;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\WebsiteFactory;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Theme\Model\Theme as Theme;

class InstallData implements InstallDataInterface
{
    const THEME = 'Daytours/last';

    /**
     * @var Registry
     */
    protected $_registry;
    /**
     * @var WebsiteFactory
     */
    private $websiteFactory;
    /**
     * @var StoreFactory
     */
    private $storeFactory;
    /**
     * @var Store
     */
    private $storeResourceModel;
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_resourceConfig;

    /**
     * @var Theme
     */
    protected $_themeModel;

    public function __construct(
        Registry $registry,
        WebsiteFactory $websiteFactory,
        Store $storeResourceModel,
        StoreFactory $storeFactory,
        ManagerInterface $eventManager,
        Config $resourceConfig,
        EncryptorInterface $encryptor,
        Theme $themeModel
    )
    {

        $this->_registry = $registry;
        $this->websiteFactory = $websiteFactory;
        $this->storeFactory = $storeFactory;
        $this->storeResourceModel = $storeResourceModel;
        $this->eventManager = $eventManager;
        $this->_resourceConfig = $resourceConfig;
        $this->_encryptor = $encryptor;
        $this->_themeModel = $themeModel;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->_registry->register("isSecureArea", true);

        $this->removeStoreViews();
        $this->renameStoreViewByDefault();

        $storeViews = [
            [
                'code' => 'spanish',
                'name' => 'Español',
                'locale' => 'es_AR'
            ],
            [
                'code' => 'portuguese',
                'name' => 'Portugues',
                'locale' => 'pt_BR'
            ],
            [
                'code' => 'french',
                'name' => 'Frances',
                'locale' => 'fr_FR'
            ],
        ];

        foreach ($storeViews as $storeView) {
            //Store views for colombia (Colombia is store by default)
            $this->createStoreViews($storeView);
        }

        $this->setCurrencies();
        $this->configurePaypalAccount();
        $this->configureBaintreeAccount();
        $this->configureEbanxCountry();
        $setup->endSetup();
    }


    /**
     * Remove store views
     *
     * @return void
     */
    protected function removeStoreViews()
    {
        $store_views = $this->storeFactory->create()->getCollection();
        foreach ($store_views as $store_view) {
            if ($store_view->getId() > 1) {
                $store_view->delete();
            }
        }
    }

    /**
     * Rename storeview default to Colombia english
     */

    private function renameStoreViewByDefault(){
        $storeView = $this->storeFactory->create()->load(1);
        $storeView->setName('English');
        $storeView->setCode('english');
        $storeView->setData('is_active','1');
        $storeView->save();
        $this->assignTheme('english');
    }

    private function createStoreViews($storeView){
        // group id 1 = store by default
        // website id 1 = website by default
        //$group = $this->groupFactory->create()->load(1);
        //$storeViewModel = $this->groupFactory->create();
        $website = $this->websiteFactory->create()->load(1);

        $store = $this->storeFactory->create();
        $store->setCode($storeView['code']);
        $store->setName($storeView['name']);
        $store->setWebsite($website);
        $store->setData('is_active','1');
        $store->setGroupId(1);
        $this->storeResourceModel->save($store);
        $this->eventManager->dispatch('store_add', ['store' => $store]);
        $this->setLocaleOptionByStoreView($storeView['code'],$storeView['locale']);
        $this->assignTheme($storeView['code']);
    }

    private function setLocaleOptionByStoreView($code,$locale){
        $storeView = $this->storeFactory->create()->load($code,'code');
        $this->_resourceConfig->saveConfig('general/locale/code',$locale,'stores',$storeView->getId());
    }

    private function setCurrencies(){
        $this->_resourceConfig->saveConfig('currency/options/allow','USD,ARS,SAR,BRL,GBP,CAD,CLP,COP,EUR','default',0);
    }

    private function configurePaypalAccount(){
        $this->_resourceConfig->saveConfig('payment/paypal_express_bml/active','1','default',0);
        $this->_resourceConfig->saveConfig('paypal/wpp/api_username',$this->_encryptor->encrypt('josecarlosf-facilitator_api1.onetree.com'),'default',0);
        $this->_resourceConfig->saveConfig('paypal/wpp/api_password',$this->_encryptor->encrypt('PJ6ZRSTE5G3T9A9Q'),'default',0);
        $this->_resourceConfig->saveConfig('paypal/wpp/api_signature',$this->_encryptor->encrypt('AaDGXKRfJtkfKjNa8l02wAqao9BiAScsOOHSv8vIZWywPw1zHkwm-S6a'),'default',0);
        $this->_resourceConfig->saveConfig('paypal/wpp/sandbox_flag','1','default',0);
        $this->_resourceConfig->saveConfig('payment/paypal_express/allowspecific','1','default',0);
        $this->_resourceConfig->saveConfig('payment/paypal_express/specificcountry','AF,AX,AL,DZ,AS,AD,AO,AI,AQ,AG,AR,AM,AW,AU,AT,AZ,BS,BH,BD,BB,BY,BE,BZ,BJ,BM,BT,BO,BA,BW,BV,IO,VG,BN,BG,BF,BI,KH,CM,CA,CV,KY,CF,TD,CL,CN,CX,CC,CO,KM,CG,CD,CK,CR,CI,HR,CU,CY,CZ,DK,DJ,DM,DO,EC,EG,SV,GQ,ER,EE,ET,FK,FO,FJ,FI,FR,GF,PF,TF,GA,GM,GE,DE,GH,GI,GR,GL,GD,GP,GU,GT,GG,GN,GW,GY,HT,HM,HN,HK,HU,IS,IN,ID,IR,IQ,IE,IM,IL,IT,JM,JP,JE,JO,KZ,KE,KI,KW,KG,LA,LV,LB,LS,LR,LY,LI,LT,LU,MO,MK,MG,MW,MY,MV,ML,MT,MH,MQ,MR,MU,YT,MX,FM,MD,MC,MN,ME,MS,MA,MZ,MM,NA,NR,NP,NL,AN,NC,NZ,NI,NE,NG,NU,NF,MP,KP,NO,OM,PK,PW,PS,PA,PG,PY,PE,PH,PN,PL,PT,QA,RE,RO,RU,RW,WS,SM,ST,SA,SN,RS,SC,SL,SG,SK,SI,SB,SO,ZA,GS,KR,ES,LK,BL,SH,KN,LC,MF,PM,VC,SD,SR,SJ,SZ,SE,CH,SY,TW,TJ,TZ,TH,TL,TG,TK,TO,TT,TN,TR,TM,TC,TV,UG,UA,AE,GB,US,UY,UM,VI,UZ,VU,VA,VE,VN,WF,EH,YE,ZM,ZW','default',0);
        $this->_resourceConfig->saveConfig('payment/paypal_billing_agreement/allowspecific','1','default',0);
        $this->_resourceConfig->saveConfig('payment/paypal_billing_agreement/specificcountry','AF,AX,AL,DZ,AS,AD,AO,AI,AQ,AG,AR,AM,AW,AU,AT,AZ,BS,BH,BD,BB,BY,BE,BZ,BJ,BM,BT,BO,BA,BW,BV,IO,VG,BN,BG,BF,BI,KH,CM,CA,CV,KY,CF,TD,CL,CN,CX,CC,CO,KM,CG,CD,CK,CR,CI,HR,CU,CY,CZ,DK,DJ,DM,DO,EC,EG,SV,GQ,ER,EE,ET,FK,FO,FJ,FI,FR,GF,PF,TF,GA,GM,GE,DE,GH,GI,GR,GL,GD,GP,GU,GT,GG,GN,GW,GY,HT,HM,HN,HK,HU,IS,IN,ID,IR,IQ,IE,IM,IL,IT,JM,JP,JE,JO,KZ,KE,KI,KW,KG,LA,LV,LB,LS,LR,LY,LI,LT,LU,MO,MK,MG,MW,MY,MV,ML,MT,MH,MQ,MR,MU,YT,MX,FM,MD,MC,MN,ME,MS,MA,MZ,MM,NA,NR,NP,NL,AN,NC,NZ,NI,NE,NG,NU,NF,MP,KP,NO,OM,PK,PW,PS,PA,PG,PY,PE,PH,PN,PL,PT,QA,RE,RO,RU,RW,WS,SM,ST,SA,SN,RS,SC,SL,SG,SK,SI,SB,SO,ZA,GS,KR,ES,LK,BL,SH,KN,LC,MF,PM,VC,SD,SR,SJ,SZ,SE,CH,SY,TW,TJ,TZ,TH,TL,TG,TK,TO,TT,TN,TR,TM,TC,TV,UG,UA,AE,GB,US,UY,UM,VI,UZ,VU,VA,VE,VN,WF,EH,YE,ZM,ZW','default',0);
    }

    private function configureBaintreeAccount(){
        $this->_resourceConfig->saveConfig('payment/braintree/merchant_id','xk2sk3wxdrfqhrcj','default',0);
        $this->_resourceConfig->saveConfig('payment/braintree/public_key',$this->_encryptor->encrypt('gpm76m33dkqkffq2'),'default',0);
        $this->_resourceConfig->saveConfig('payment/braintree/private_key',$this->_encryptor->encrypt('b1b32a614ef053f6adfa58c141bef5f7'),'default',0);
        $this->_resourceConfig->saveConfig('payment/braintree/merchant_account_id','xk2sk3wxdrfqhrcj','default',0);
        $this->_resourceConfig->saveConfig('payment/braintree/environment','sandbox','default',0);
        $this->_resourceConfig->saveConfig('payment/braintree/active','1','default',0);
        $this->_resourceConfig->saveConfig('payment/braintree/allowspecific','1','default',0);
        $this->_resourceConfig->saveConfig('payment/braintree/specificcountry','AF,AX,AL,DZ,AS,AD,AO,AI,AQ,AG,AR,AM,AW,AU,AT,AZ,BS,BH,BD,BB,BY,BE,BZ,BJ,BM,BT,BO,BA,BW,BV,IO,VG,BN,BG,BF,BI,KH,CM,CA,CV,KY,CF,TD,CL,CN,CX,CC,CO,KM,CG,CD,CK,CR,CI,HR,CU,CY,CZ,DK,DJ,DM,DO,EC,EG,SV,GQ,ER,EE,ET,FK,FO,FJ,FI,FR,GF,PF,TF,GA,GM,GE,DE,GH,GI,GR,GL,GD,GP,GU,GT,GG,GN,GW,GY,HT,HM,HN,HK,HU,IS,IN,ID,IR,IQ,IE,IM,IL,IT,JM,JP,JE,JO,KZ,KE,KI,KW,KG,LA,LV,LB,LS,LR,LY,LI,LT,LU,MO,MK,MG,MW,MY,MV,ML,MT,MH,MQ,MR,MU,YT,MX,FM,MD,MC,MN,ME,MS,MA,MZ,MM,NA,NR,NP,NL,AN,NC,NZ,NI,NE,NG,NU,NF,MP,KP,NO,OM,PK,PW,PS,PA,PG,PY,PE,PH,PN,PL,PT,QA,RE,RO,RU,RW,WS,SM,ST,SA,SN,RS,SC,SL,SG,SK,SI,SB,SO,ZA,GS,KR,ES,LK,BL,SH,KN,LC,MF,PM,VC,SD,SR,SJ,SZ,SE,CH,SY,TW,TJ,TZ,TH,TL,TG,TK,TO,TT,TN,TR,TM,TC,TV,UG,UA,AE,GB,US,UY,UM,VI,UZ,VU,VA,VE,VN,WF,EH,YE,ZM,ZW','default',0);

    }

    private function configureEbanxCountry(){
        /*express*/
        $this->_resourceConfig->saveConfig('payment/express/secret_key',$this->_encryptor->encrypt('test_ik_togSVzusR0aRb3y9QhKRBw'),'default',0);
        $this->_resourceConfig->saveConfig('payment/express/test_mode','1','default',0);
        $this->_resourceConfig->saveConfig('payment/express/active','0','default',0);
        $this->_resourceConfig->saveConfig('payment/express/allowspecific','1','default',0);
        $this->_resourceConfig->saveConfig('payment/express/specificcountry','BR','default',0);
        /*standard*/
        $this->_resourceConfig->saveConfig('payment/standard/secret_key',$this->_encryptor->encrypt('test_ik_togSVzusR0aRb3y9QhKRBw'),'default',0);
        $this->_resourceConfig->saveConfig('payment/standard/test_mode','1','default',0);
        $this->_resourceConfig->saveConfig('payment/standard/active','1','default',0);
        $this->_resourceConfig->saveConfig('payment/standard/allowspecific','1','default',0);
        $this->_resourceConfig->saveConfig('payment/standard/specificcountry','BR','default',0);

    }

    protected function assignTheme($codeStoreView)
    {
        $theme_daytours = $this->_themeModel->load(self::THEME,"code");
        $storeView = $this->storeFactory->create()->load($codeStoreView,'code');
        $this->_resourceConfig->saveConfig('design/theme/theme_id',$theme_daytours->getThemeId(),'stores',$storeView->getId());
    }

}