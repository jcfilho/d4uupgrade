## Agregar y/o actualizar un store view
Para agregar o actualizar un store view tenemos que instanciar un install a partir de su factory
`\Onetree\ImportDataFixtures\Model\Factory\InstallStoreViewFactory`
Con el factory se crea el install usando el método createInstall
```php
$storeViewInstall = $this->installStoreViewFactory->createInstall();
$storeViewInstall->install(['Onetree_SetupTheme::fixtures/csv/stores_view/stores_view_install.csv']);
```
## Agregar y/o actualizar un cms block
Para agregar o actualizar un block cms tenemos que instanciar un install a partir de su factory
`\Onetree\ImportDataFixtures\Model\Factory\InstallBlockFactory`
Con el factory se crea el install usando el método createInstall
```php
$installBlock = $this->installBlockFactory->createInstall();
$installBlock->install(['Onetree_SetupTheme::fixtures/csv/blocks/cms_block_0.1.1.csv']);
```
## Agregar y/o actualizar una cms page
Para agregar o actualizar una cms page tenemos que instanciar un install a partir de su factory
`\Onetree\ImportDataFixtures\Model\Factory\InstallPageFactory`
Con el factory se crea el install usando el método createInstall
```php
$installPage = $this->installPageFactory->createInstall();
$installPage->install(['Onetree_SetupTheme::fixtures/csv/pages/cms_page_0.1.0.csv']);
```
## Agregar y/o categories
Para agregar o actualizar categories tenemos que instanciar un install a partir de su factory
`\Onetree\ImportDataFixtures\Model\Factory\InstallCategoryFactory`
Con el factory se crea el install usando el método createInstall
```php
$installCategoryFactory = $this->installCategoryFactory->createInstall();
$installCategoryFactory->install(['Onetree_SetupTheme::fixtures/csv/categories/categories_0.1.2.csv']);
```
## Copiar imágenes al folder pub
Para esto usamos el helper `\Onetree\SetupTheme\Helper\Deploy`.
En la raiz de tu módulo te creas el folder `pub/media/` y ahí podes tener varios niveles de 
carpetas.
```php
/**
 * copy images from app/code/Onetree_SetupTheme/pub to pub directory
 */
if (version_compare($context->getVersion(), '0.1.4') < 0) {
    $pubPath = __DIR__.'/../pub';
    $this->pubDeployer->deployFolder($pubPath);
}
```