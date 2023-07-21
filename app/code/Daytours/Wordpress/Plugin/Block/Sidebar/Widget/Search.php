<?php
/**
 * Created by PhpStorm.
 * User: filho
 * Date: 6/11/18
 * Time: 11:18 AM
 */

namespace Daytours\Wordpress\Plugin\Block\Sidebar\Widget;

class Search
{

    public function aroundGetSearchTerm(\FishPig\WordPress\Block\Sidebar\Widget\Search $subject, \Closure $proceed)
    {
        $result = '';
        if( $subject->getRequest()->getParam('s') ){
            if( !empty($subject->getRequest()->getParam('s')) ){
                $result = $subject->getRequest()->getParam('s');
            }
        }

        return $result;
    }

}