<?php

/**
 * RegenerateProductRewrites.php
 *
 * @package OlegKoval_RegenerateUrlRewrites
 * @author Oleg Koval <contact@olegkoval.com>
 * @copyright 2017-2067 Oleg Koval
 * @license OSL-3.0, AFL-3.0
 */

namespace Daytours\URLRewriteByLanguageSite\Model;

use Exception;

class RegenerateProductRewrites extends \OlegKoval\RegenerateUrlRewrites\Model\RegenerateProductRewrites
{
    /**
     * Regenerate Url Rewrites for specific product in specific store
     * @param $entity
     * @param int $storeId
     * @return $this
     */
    public function processProduct($entity, $storeId = 0)
    {

        $entity->setStoreId($storeId)->setData('url_path', null);

        if ($this->regenerateOptions['saveOldUrls']) {
            $entity->setData('save_rewrites_history', true);
        }

        // reset url_path to null, we need this to set a flag to use a Url Rewrites:
        // see logic in core Product Url model: \Magento\Catalog\Model\Product\Url::getUrl()
        // if "request_path" is not null or equal to "false" then Magento do not serach and do not use Url Rewrites
        $updateAttributes = ['url_path' => null];
        if (!$this->regenerateOptions['noRegenUrlKey']) {
            $generatedKey = $this->_getProductUrlPathGenerator()->getUrlKey($entity->setUrlKey(null));
            $updateAttributes['url_key'] = str_replace('packages', 'paquetes', $generatedKey);
        }

        $this->_getProductAction()->updateAttributes(
            [$entity->getId()],
            $updateAttributes,
            $storeId
        );

        $urlRewrites = $this->_getProductUrlRewriteGenerator()->generate($entity);
        $urlRewrites = $this->helper->sanitizeProductUrlRewrites($urlRewrites);

        $tablaTraduccionStrJSON = file_get_contents("https://fc9bfe9ef1.nxcli.net/pub/media/traducciones/productos.json");
        $tablaTraduccionArrJSON = json_decode($tablaTraduccionStrJSON);
        if (!empty($urlRewrites)) {
            foreach ($urlRewrites as $ur) {
                $reqPath = $ur->getRequestPath();
                $reqPathParts = explode("/", $reqPath);
                $reqPath = $reqPathParts[count($reqPathParts) - 2] . "/"; 
                if (!empty($tablaTraduccionArrJSON) && ($storeId == 6 || $storeId == 7 || $storeId == 8 || $storeId == 5)) {
                    if ($storeId == 6) {
                        $reqPath = $this->traducirPalabra($tablaTraduccionArrJSON, "es", $ur);
                    } else if ($storeId == 7) {
                        $reqPath = $this->traducirPalabra($tablaTraduccionArrJSON, "pt", $ur);
                    } else if ($storeId == 8) {
                        $reqPath = $this->traducirPalabra($tablaTraduccionArrJSON, "fr", $ur);
                    } else {
                        $reqPath = $this->traducirPalabra($tablaTraduccionArrJSON, "", $ur);
                    }
                }
                $ur->setRequestPath($reqPath);
            }
            $this->saveUrlRewrites(
                $urlRewrites,
                [['entity_type' => $this->entityType, 'entity_id' => $entity->getId(), 'store_id' => $storeId]]
            );
        }

        $this->progressBarProgress++;
        return $this;
    }

    private function traducirPalabra($tablaTraduccion, $languageCode, $urlRewrite)
    {
        $urlPart = $urlRewrite->getRequestPath();
        $urlId = $urlRewrite->getEntityId();
        if (empty($urlPart)) {
            return $urlPart;
        }
        foreach ($tablaTraduccion as $traduccion) {
            if ($urlId == $traduccion->id) {
                $newUrl = $urlPart;
                if ($languageCode == "es") {
                    $newUrl = strtolower($traduccion->es);
                } else if ($languageCode == "fr") {
                    $newUrl = strtolower($traduccion->fr);
                } else if ($languageCode == "pt") {
                    $newUrl = strtolower($traduccion->pt);
                } else {
                    $newUrl = strtolower($traduccion->nombre);
                }
                $newUrl = preg_replace("/[+'.-:’]/", " ", $newUrl);
                $newUrl = $this->eliminar_tildes($newUrl);
                //$newUrl = preg_replace("/^(.(?![a-zA-Z -]))+$/","",$newUrl);
                $newUrl = preg_replace("/\s+/", " ", $newUrl);
                //$newUrl = str_replace(array(" /"," "),array("/","-"),$newUrl);
                //$newUrl = str_replace(".","",$newUrl);
                $newUrl = $newUrl . "/";
                return str_replace(array(" /", " "), array("/", "-"), $newUrl);
                //return str_replace($traduccion->categoria, $newUrl, $urlPart);
            }
        }
        return $urlPart;
    }

    private function eliminar_tildes($cadena)
    {

        //Ahora reemplazamos las letras
        $cadena = str_replace(
            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
            $cadena
        );

        $cadena = str_replace(
            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
            $cadena
        );

        $cadena = str_replace(
            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
            $cadena
        );

        $cadena = str_replace(
            array('õ', 'ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
            array('o', 'o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
            $cadena
        );

        $cadena = str_replace(
            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
            $cadena
        );

        $cadena = str_replace(
            array('ñ', 'Ñ', 'ç', 'Ç'),
            array('n', 'N', 'c', 'C'),
            $cadena
        );

        return $cadena;
    }
}
