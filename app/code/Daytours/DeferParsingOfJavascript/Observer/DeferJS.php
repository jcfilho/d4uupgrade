<?php

namespace Daytours\DeferParsingOfJavascript\Observer;

use Magento\Framework\View\Asset\PreProcessor\Minify;

class DeferJS implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return boolean|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $response = $observer->getEvent()->getResponse();
        $htmlContent = $response->getBody();

        if (stripos($htmlContent, '<!DOCTYPE html') !== false) {
            $headers = $response->getHeaders()->toArray();
            if (
                array_key_exists('Content-Type', $headers)
                && $headers['Content-Type'] == 'application/json'
            ) {
                return false;
            }

            $htmlContent = $this->addDeferToScripts($htmlContent);
            $htmlContent = $this->filterCssLinkrel($htmlContent);
            //$htmlContent = $this->lazyLoadSupport($htmlContent);
            $htmlContent = $this->abrirNuevaPestania($htmlContent);
            
            //$htmlContent = "<hmtl>error controlado</hmtl>";


            // Set the body with the new HTML content
            $response->setBody($htmlContent);
        }
    }


    private function abrirNuevaPestania($htmlContent){
        $avoid = array(
            'href="#',
            'customer/account'
        );
        if($this->str_contain($htmlContent,array(
            '<title>Tours and Activities in South America | Daytours4u</title>',
            '<title>Tours y Actividades en América del Sur | Daytours4u</title>',
            '<title>Tours e Atividades na América do Sul | Daytours4u</title>',
            '<title>Tours et Activités en Amérique du Sud | Daytours4u</title>'
            ))){
                preg_match_all("/<a\b[^>]*>([\s\S]*?)<\/a>/", $htmlContent, $output_array);
                foreach ($output_array[0] as $currentLink) {
                    if (strpos($currentLink, 'target="_blank"') === false && $this->str_contain($currentLink, $avoid) === false) {
                        $newLink = str_replace('<a ', '<a target="_blank" ', $currentLink);
                        $htmlContent = str_replace($currentLink, $newLink, $htmlContent);
                    }
                }
                return $htmlContent;
            }
            else{
                return $htmlContent;
            }
    }


    /**
     * Este metodo buscara a todas aquellas funciones no sensibles y agregara defer en su etiqueta
     *
     * @param string $htmlContent
     * @return string
     */
    private function addDeferToScripts($htmlContent)
    {

        $avoid = array('Recaptcha','tawk','zdassets');

        //Excepciones
        if($this->str_contain($htmlContent,array(
            // '<title>Contact Us</title>',
            // '<title>Contact Us Frances</title>',
            // '<title>Contactanos</title>',
            '<li class="join-link">'
            ))){
                $avoid = array_filter($avoid,function($c){
                    return $c != 'Recaptcha';
                });
            }

        preg_match_all("/<script\b[^>]*>([\s\S]*?)<\/script>/", $htmlContent, $output_array);
        foreach ($output_array[0] as $currentScript) {
            if($this->str_contain($currentScript,$avoid)){
                $htmlContent = str_replace($currentScript, "", $htmlContent);
            }
            else{
                if (
                    strpos($currentScript, "require")  === false && 
                    strpos($currentScript, "defer")  === false
                    ) {
                    $newScript = str_replace('<script', '<script defer', $currentScript);
                    $htmlContent = str_replace($currentScript, $newScript, $htmlContent);
                }
            }
        }
        return $htmlContent;
    }

        /**
     * Este metodo buscara a todas aquellas funciones no sensibles y agregara defer en su etiqueta
     *
     * @param string $htmlContent
     * @return string
     */
    private function filterCssLinkrel($htmlContent)
    {

        $avoid = array(
            'boostrap-tiny.min.css',
            'Ves_Megamenu/css/styles-l.css',
            'Ves_Megamenu/css/styles-m.css',
            'Ves_All/css/font-awesome.min.css',
            'Ves_All/fonts/fontawesome-webfont.woff2?v=4.7.0',
            'Magebay_Marketplace/css/dashboard-theme.css',
            'Magebay_Marketplace/css/d-custom.css',
            'Amasty_Affiliate/css/default.css',
            'box',
            'css/print.css',
            'Ves_PageBuilder/css/styles.css',
            'css/print-daytours.css',
            'marketplace-become.css',
            'Ves_Magemenu/css/animate.min.css',
            'Magebay_Marketplace/css/custom.css'
        );

        $pospose = array(
            'owl',
            'animate',
            'gallery',
            'responsive',
            'daytours-all',
            'caroulsel',
            'Social',
            'tiny',
            'default.min',
            'print',
            'marketplace-become',
            'blog'
        );
        
        preg_match_all("/<link([^>]+)>/", $htmlContent, $output_array);
        $cssArray = "";
        foreach ($output_array[0] as $current) {
            if(!$this->str_contain($current,$avoid)){
                if($this->str_contain($current,$pospose)){
                    $cssArray .= $current;
                    $htmlContent = str_replace($current, "", $htmlContent);
                }
            }
            else{
                $htmlContent = str_replace($current, "", $htmlContent);
            }
        }

        return str_replace("</body>",$cssArray.'</body>',$htmlContent);
    }

    private function str_contain($string,$arrayReferencesToSearch){
        foreach($arrayReferencesToSearch as $current){
            if(strpos($string,$current) !== false){
                return true;
            }
        }
        return false;
    }

}
