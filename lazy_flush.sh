php bin/magento app:config:import
rm -r var/*
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:clean
php bin/magento cache:flush
rm -r pub/static
php bin/magento setup:static-content:deploy en_US
php bin/magento indexer:reindex
php bin/magento cache:clean
php bin/magento cache:flush
