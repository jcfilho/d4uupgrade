<?php
/**
 * Result
 *
 * @copyright Copyright © 2019 Onetree. All rights reserved.
 * @author    josecarlosf@onetree.com
 */

namespace Magenest\Customize\Plugin\Block\Search;

use Magento\CatalogSearch\Helper\Data;
use \Magento\Search\Model\QueryFactory;

class Result
{
    /**
     * @var Data
     */
    private $catalogSearchData;
    /**
     * @var QueryFactory
     */
    private $query;

    /**
     * Result constructor.
     * @param Data $catalogSearchData
     * @param QueryFactory $query
     */
    public function __construct(
        Data $catalogSearchData,
        QueryFactory $query
    )
    {
        $this->catalogSearchData = $catalogSearchData;
        $this->query = $query;
    }

    public function afterGetSearchQueryText(\Magento\CatalogSearch\Block\Result $subject, $result){
        if($this->query->get()->getNumResults() > 0){
            //return  __("Search results for '%1'", $this->catalogSearchData->getEscapedQueryText());
            return  __("results for '%1'", $this->catalogSearchData->getEscapedQueryText());
        }else{
            //return  __("Oops! No Search results for '%1'", $this->catalogSearchData->getEscapedQueryText());
            return  __("results for '%1'", $this->catalogSearchData->getEscapedQueryText());
        }
    }
}