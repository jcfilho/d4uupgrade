rm -rf var/*
rm -rf generated/*
rm -rf pub/static/*
php bin/magento app:config:import
#php bin/magento setup:upgrade --keep-generated
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy en_US es_AR fr_FR pt_BR
#php bin/magento setup:static-content:deploy en_US -f
php bin/magento indexer:reindex
php bin/magento cache:clean
php bin/magento cache:flush
