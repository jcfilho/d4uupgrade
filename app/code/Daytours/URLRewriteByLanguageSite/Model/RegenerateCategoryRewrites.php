<?php

/**
 * RegenerateCategoryRewrites.php
 *
 * @package OlegKoval_RegenerateUrlRewrites
 * @author Oleg Koval <contact@olegkoval.com>
 * @copyright 2017-2067 Oleg Koval
 * @license OSL-3.0, AFL-3.0
 */

namespace Daytours\URLRewriteByLanguageSite\Model;

class RegenerateCategoryRewrites extends \OlegKoval\RegenerateUrlRewrites\Model\RegenerateCategoryRewrites
{
    /**
     * Process category Url Rewrites re-generation
     * @param $category
     * @param int $storeId
     * @return $this
     */
    protected function categoryProcess($category, $storeId = 0)
    {
        $tablaTraduccionStrJSON = file_get_contents("https://fc9bfe9ef1.nxcli.net/pub/media/traducciones/categorias.json");
        $tablaTraduccionArrJSON = json_decode($tablaTraduccionStrJSON);
        $category->setStoreId($storeId);

        if ($this->regenerateOptions['saveOldUrls']) {
            $category->setData('save_rewrites_history', true);
        }

        if (!$this->regenerateOptions['noRegenUrlKey']) {
            $category->setOrigData('url_key', null);
            $urlKey = $this->_getCategoryUrlPathGenerator()->getUrlKey($category->setUrlKey(null));
            $category->setUrlKey(str_replace('packages', 'paquetes', $urlKey));
            $category->getResource()->saveAttribute($category, 'url_key');
        }
        $urlPath = $this->_getCategoryUrlPathGenerator()->getUrlPath($category);
        $category->setUrlPath(str_replace('packages', 'paquetes', $urlPath));
        $category->getResource()->saveAttribute($category, 'url_path');

        $category->setChangedProductIds(true);
        $categoryUrlRewriteResult = $this->_getCategoryUrlRewriteGenerator()->generate($category, true);
        if (!empty($categoryUrlRewriteResult)) {
            if (!empty($tablaTraduccionArrJSON) && ($storeId == 6 || $storeId == 7 || $storeId == 8 || $storeId == 5)){
                foreach ($categoryUrlRewriteResult as $cat) {
                    $reqPath = $cat->getRequestPath();
                    if ($storeId == 6) {
                        $cat->setRequestPath($this->traducirURL($tablaTraduccionArrJSON,"es",$reqPath));
                    } else if ($storeId == 7) {
                        $cat->setRequestPath($this->traducirURL($tablaTraduccionArrJSON,"pt",$reqPath));
                    } else if ($storeId == 8) {
                        $cat->setRequestPath($this->traducirURL($tablaTraduccionArrJSON,"fr",$reqPath));
                    }
                }
                $this->saveUrlRewrites($categoryUrlRewriteResult);
            }
        }

        // if config option "Use Categories Path for Product URLs" is "Yes" then regenerate product urls
        if ($this->helper->useCategoriesPathForProductUrls($storeId)) {
            $productsIds = $this->_getCategoriesProductsIds($category->getAllChildren());
            if (!empty($productsIds)) {
                $this->regenerateProductRewrites->regenerateOptions = $this->regenerateOptions;
                $this->regenerateProductRewrites->regenerateOptions['showProgress'] = false;
                $this->regenerateProductRewrites->regenerateProductsRangeUrlRewrites($productsIds, $storeId);
            }
        }

        //frees memory for maps that are self-initialized in multiple classes that were called by the generators
        $this->_resetUrlRewritesDataMaps($category);

        $this->progressBarProgress++;

        return $this;
    }

    private function traducirURL($tablaTraduccion, $languageCode, $url)
    {
        $urlParts = explode("/", $url);
        foreach ($urlParts as $urlPart) {
            $urlPartTraducida = $this->traducirPalabra($tablaTraduccion, $languageCode, $urlPart);
            $url = str_replace($urlPart, $urlPartTraducida, $url);
        }
        return $url;
    }

    private function traducirPalabra($tablaTraduccion, $languageCode, $urlPart)
    {
        if(empty($urlPart)){ return $urlPart;}
        foreach($tablaTraduccion as $traduccion){
            $urlPartSinHtml = str_replace(".html","",$urlPart);
            if(strcmp($urlPartSinHtml,$traduccion->categoria) == 0){
                if ($languageCode == "es") {
                    $newUrl = strtolower($traduccion->es);
                } else if ($languageCode == "fr") {
                    $newUrl = strtolower($traduccion->fr);
                } else if ($languageCode == "pt") {
                    $newUrl = strtolower($traduccion->pt);
                }
                else{
                    $newUrl = strtolower($traduccion->categoria);
                }
                $newUrl = str_replace("'", "-", $newUrl);
                $newUrl = str_replace(" ","-",$newUrl);
                $newUrl = str_replace(".","",$newUrl);
                $newUrl = $this->eliminar_tildes($newUrl);
                return str_replace($traduccion->categoria,$newUrl, $urlPart) ;
            }
        }
        return $urlPart;
    }

    private function eliminar_tildes($cadena){

        //Codificamos la cadena en formato utf8 en caso de que nos de errores
        //$cadena = utf8_encode($cadena);
    
        //Ahora reemplazamos las letras
        $cadena = str_replace(
            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
            $cadena
        );
    
        $cadena = str_replace(
            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
            $cadena );
    
        $cadena = str_replace(
            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
            $cadena );
    
        $cadena = str_replace(
            array('õ','ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
            array('o','o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
            $cadena );
    
        $cadena = str_replace(
            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
            $cadena );
    
        $cadena = str_replace(
            array('ñ', 'Ñ', 'ç', 'Ç'),
            array('n', 'N', 'c', 'C'),
            $cadena
        );
    
        return $cadena;
    }
}
