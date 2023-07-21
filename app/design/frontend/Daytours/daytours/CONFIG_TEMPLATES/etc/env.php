<?php
return array (
  'backend' => 
  array (
    'frontName' => 'admin',
  ),
  'crypt' => 
  array (
    'key' => '1d176176aecfee9217c63f2877f2ae8f',
  ),
  'db' => 
  array (
    'table_prefix' => '',
    'connection' => 
    array (
      'default' => 
      array (
        'host' => 'localhost',
        'dbname' => 'daytours',
        'username' => 'root',
        'password' => '',
        'active' => '1',
      ),
    ),
  ),
  'resource' => 
  array (
    'default_setup' => 
    array (
      'connection' => 'default',
    ),
  ),
  'x-frame-options' => 'SAMEORIGIN',
  'MAGE_MODE' => 'developer',
  'session' => 
  array (
    'save' => 'files',
  ),
  'cache_types' => 
  array (
    'config' => 1,
    'layout' => 1,
    'block_html' => 1,
    'collections' => 1,
    'reflection' => 1,
    'db_ddl' => 1,
    'eav' => 1,
    'customer_notification' => 1,
    'config_integration' => 1,
    'config_integration_api' => 1,
    'full_page' => 1,
    'translate' => 1,
    'config_webservice' => 1,
    'compiled_config' => 1,
  ),
  'install' => 
  array (
    'date' => 'Wed, 14 Mar 2018 01:43:49 +0000',
  ),
  'system' => 
  array (
    'default' => 
    array (
      'dev' => 
      array (
        'js' => 
        array (
          'session_storage_key' => 'collected_errors',
        ),
        'restrict' => 
        array (
          'allow_ips' => NULL,
        ),
      ),
      'system' => 
      array (
        'smtp' => 
        array (
          'host' => 'localhost',
          'port' => '25',
        ),
      ),
      'web' => 
      array (
        'unsecure' => 
        array (
          'base_url' => 'http://localhost/daytours/',
          'base_link_url' => '{{unsecure_base_url}}',
          'base_static_url' => NULL,
          'base_media_url' => NULL,
        ),
        'secure' => 
        array (
          'base_url' => 'https://localhost/daytours/',
          'base_link_url' => '{{secure_base_url}}',
          'base_static_url' => NULL,
          'base_media_url' => NULL,
        ),
        'default' => 
        array (
          'front' => 'cms',
        ),
        'cookie' => 
        array (
          'cookie_path' => NULL,
          'cookie_domain' => NULL,
        ),
      ),
      'admin' => 
      array (
        'url' => 
        array (
          'custom' => NULL,
        ),
      ),
      'currency' => 
      array (
        'import' => 
        array (
          'error_email' => NULL,
        ),
      ),
      'customer' => 
      array (
        'create_account' => 
        array (
          'email_domain' => 'example.com',
        ),
      ),
      'payment' => 
      array (
        'authorizenet_directpost' => 
        array (
          'debug' => '0',
          'email_customer' => '0',
          'login' => NULL,
          'merchant_email' => NULL,
          'test' => '1',
          'trans_key' => NULL,
          'trans_md5' => NULL,
          'cgi_url' => 'https://secure.authorize.net/gateway/transact.dll',
          'cgi_url_td' => 'https://api2.authorize.net/xml/v1/request.api',
        ),
        'payflowpro' => 
        array (
          'user' => NULL,
          'pwd' => NULL,
        ),
        'payflow_link' => 
        array (
          'pwd' => NULL,
          'url_method' => 'GET',
        ),
        'payflow_advanced' => 
        array (
          'user' => 'PayPal',
          'pwd' => NULL,
          'url_method' => 'GET',
        ),
        'braintree' => 
        array (
          'private_key' => '0:2:9DZDHbZxgepVy6KSNFXZHevmvGmAVk0K:MZfD3Zm//H2q8MYePcEpG8NycgCKzzjHc7qkjBnDmAQ=',
          'merchant_id' => 'xk2sk3wxdrfqhrcj',
          'merchant_account_id' => 'xk2sk3wxdrfqhrcj',
        ),
      ),
      'catalog' => 
      array (
        'productalert_cron' => 
        array (
          'error_email' => NULL,
        ),
      ),
      'contact' => 
      array (
        'email' => 
        array (
          'recipient_email' => 'hello@example.com',
        ),
      ),
      'carriers' => 
      array (
        'dhl' => 
        array (
          'account' => NULL,
          'gateway_url' => 'https://xmlpi-ea.dhl.com/XMLShippingServlet',
          'id' => NULL,
          'password' => NULL,
        ),
        'fedex' => 
        array (
          'account' => NULL,
          'meter_number' => NULL,
          'key' => NULL,
          'password' => NULL,
          'sandbox_mode' => '0',
          'production_webservices_url' => 'https://ws.fedex.com:443/web-services/',
          'sandbox_webservices_url' => 'https://wsbeta.fedex.com:443/web-services/',
        ),
        'ups' => 
        array (
          'access_license_number' => NULL,
          'gateway_url' => 'http://www.ups.com/using/services/rave/qcostcgi.cgi',
          'gateway_xml_url' => 'https://onlinetools.ups.com/ups.app/xml/Rate',
          'tracking_xml_url' => 'https://www.ups.com/ups.app/xml/Track',
          'username' => NULL,
          'password' => NULL,
          'is_account_live' => '0',
        ),
        'usps' => 
        array (
          'gateway_url' => 'http://production.shippingapis.com/ShippingAPI.dll',
          'gateway_secure_url' => 'https://secure.shippingapis.com/ShippingAPI.dll',
          'userid' => NULL,
          'password' => NULL,
        ),
      ),
      'trans_email' => 
      array (
        'ident_custom1' => 
        array (
          'email' => 'custom1@example.com',
          'name' => 'Custom 1',
        ),
        'ident_custom2' => 
        array (
          'email' => 'custom2@example.com',
          'name' => 'Custom 2',
        ),
        'ident_general' => 
        array (
          'email' => 'owner@example.com',
          'name' => 'Owner',
        ),
        'ident_sales' => 
        array (
          'email' => 'sales@example.com',
          'name' => 'Sales',
        ),
        'ident_support' => 
        array (
          'email' => 'support@example.com',
          'name' => 'CustomerSupport',
        ),
      ),
      'analytics' => 
      array (
        'url' => 
        array (
          'signup' => 'https://advancedreporting.rjmetrics.com/signup',
          'update' => 'https://advancedreporting.rjmetrics.com/update',
          'bi_essentials' => 'https://dashboard.rjmetrics.com/v2/magento/signup',
          'otp' => 'https://advancedreporting.rjmetrics.com/otp',
          'report' => 'https://advancedreporting.rjmetrics.com/report',
          'notify_data_changed' => 'https://advancedreporting.rjmetrics.com/report',
        ),
      ),
      'newrelicreporting' => 
      array (
        'general' => 
        array (
          'api_url' => 'https://api.newrelic.com/deployments.xml',
          'insights_api_url' => 'https://insights-collector.newrelic.com/v1/accounts/%s/events',
        ),
      ),
      'paypal' => 
      array (
        'wpp' => 
        array (
          'api_password' => '0:2:pH3pfCd3RLzVWvz6JFcdWBVjN9H10F0q:Kf2B9shS3GXbQtRQo7FbAptJ+pjVERx9/X9d12je7fk=',
          'api_signature' => '0:2:MYO6RqAarSjdgFqkxCU6Z6eCPXnvLvNP:DfAKVLkcIyIr5f7I/oLW1zcpMfVVTpf2WAcVXMJbHcUQ6f5ztVUsbU/jAtBNCNDHDum5W06YxoMoO57Oz44ilA==',
          'api_username' => '0:2:UO95wbHj82H0uIAgN5mHMttnDJTU6b6o:gRE8H5iTKr+WQLHCGre9cnoW2YuUI6j1hrWW8vxLU6jnnrMtPBVsmEfhB16a4gemMzkFeEVNGVcONY2Z3UlqUA==',
          'sandbox_flag' => '1',
        ),
        'fetch_reports' => 
        array (
          'ftp_login' => NULL,
          'ftp_password' => NULL,
        ),
      ),
      'fraud_protection' => 
      array (
        'signifyd' => 
        array (
          'api_url' => 'https://api.signifyd.com/v2/',
          'api_key' => NULL,
        ),
      ),
      'sitemap' => 
      array (
        'generate' => 
        array (
          'error_email' => NULL,
        ),
      ),
      'crontab' => 
      array (
        'default' => 
        array (
          'jobs' => 
          array (
            'analytics_subscribe' => 
            array (
              'schedule' => 
              array (
                'cron_expr' => '0 * * * *',
              ),
            ),
          ),
        ),
      ),
    ),
  ),
);
