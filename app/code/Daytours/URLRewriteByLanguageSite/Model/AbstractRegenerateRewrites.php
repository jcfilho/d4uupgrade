<?php

/**
 * AbstractRegenerateRewrites.php
 *
 * @package OlegKoval_RegenerateUrlRewrites
 * @author Oleg Koval <contact@olegkoval.com>
 * @copyright 2017-2067 Oleg Koval
 * @license OSL-3.0, AFL-3.0
 */

namespace Daytours\URLRewriteByLanguageSite\Model;

abstract class AbstractRegenerateRewrites extends \OlegKoval\RegenerateUrlRewrites\Model\AbstractRegenerateRewrites
{
    /**
     * @param array $urlRewrites
     * @return array
     */
    protected function _prepareUrlRewrites($urlRewrites)
    {
        $result = [];
        foreach ($urlRewrites as $urlRewrite) {
            $rewrite = $urlRewrite->toArray();
            if (!$this->_urlRewriteExists($rewrite)) {
                // check if same Url Rewrite already exists
                $originalRequestPath = trim($rewrite['request_path']);

                // skip empty Url Rewrites - I don't know how this possible, but it happens in Magento:
                // maybe someone did import product programmatically and product(s) name(s) are empty
                if (empty($originalRequestPath)) continue;

                // split generated Url Rewrite into parts
                $pathParts = pathinfo($originalRequestPath);

                // remove leading/trailing slashes and dots from parts
                $pathParts['dirname'] = trim($pathParts['dirname'], './');
                $pathParts['filename'] = trim($pathParts['filename'], './');

                // If the last symbol was slash - let's use it as url suffix
                $urlSuffix = substr($originalRequestPath, -1) === '/' ? '/' : '';

                // re-set Url Rewrite with sanitized parts
                $rewrite['request_path'] = $this->_mergePartsIntoRewriteRequest($pathParts, '', $urlSuffix);

                $result[] = $rewrite;
            }   
        }
        return $result;
    }
    /**
     * Check if Url Rewrite with same request path exists
     * @param array $rewrite
     * @return bool
     */
    protected function _urlRewriteExists($rewrite)
    {
        $select = $this->_getResourceConnection()->getConnection()->select()
            ->from($this->_getMainTableName(), ['url_rewrite_id'])
            ->where('entity_type = ?', $rewrite['entity_type'])
            ->where('store_id = ?', $rewrite['store_id'])
            ->where('entity_id = ?', $rewrite['entity_id']);
        return $this->_getResourceConnection()->getConnection()->fetchOne($select);
    }
}
