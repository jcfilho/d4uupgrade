<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Daytours\StoreFixer\Model;

use Exception;

/**
 * Service contract, which manage scopes
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreManager extends \Magento\Store\Model\StoreManager
{
    /**
     * {@inheritdoc}
     */
    // public function getStore($storeId = null)
    // {
    //     if (!isset($storeId) || '' === $storeId || $storeId === true) {
    //         if (null === $this->currentStoreId) {
    //             \Magento\Framework\Profiler::start('store.resolve');
    //             $this->currentStoreId = $this->storeResolver->getCurrentStoreId();
    //             \Magento\Framework\Profiler::stop('store.resolve');
    //         }

    //         $storeId = $this->currentStoreId;
    //     }

    //     if ($storeId instanceof \Magento\Store\Api\Data\StoreInterface) {
    //         return $storeId;
    //     }

    //     switch ($storeId) {
    //         case 1:
    //         case "english":
    //             $storeId = "en";
    //             break;
    //         case 2:
    //         case "spanish":
    //             $storeId = "es";
    //             break;
    //         case 3:
    //         case "french":
    //             $storeId = "fr";
    //             break;
    //         case 4:
    //         case "portuguese":
    //             $storeId = "pt";
    //             break;
    //     }

    //     $store = is_numeric($storeId)
    //         ? $this->storeRepository->getById($storeId)
    //         : $this->storeRepository->get($storeId);

    //     return $store;
    // }

    public function getStore($storeId = null)
    {
        if (!isset($storeId) || '' === $storeId || $storeId === true) {
            if (null === $this->currentStoreId) {
                \Magento\Framework\Profiler::start('store.resolve');
                $this->currentStoreId = $this->storeResolver->getCurrentStoreId();
                \Magento\Framework\Profiler::stop('store.resolve');
            }
			if (empty($this->currentStoreId) && isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'argentina4u.daytours4u.com') {
				$storeId = 1;
			} else {
				$storeId = $this->currentStoreId;
			}
        }

        if ($storeId instanceof \Magento\Store\Api\Data\StoreInterface) {
            return $storeId;
        }

		switch($storeId) {
			case "english":
				$storeId = "en";
				break;
			case "spanish":
				$storeId = "es";
				break;
			case "french":
				$storeId = "fr";
				break;
			case "portuguese":
				$storeId = "pt";
				break;
            case "":
                //throw new Exception("Error. No existe el store: ''");
                $storeId = 1;
                break;
		}

        $store = is_numeric($storeId)
            ? $this->storeRepository->getById($storeId)
            : $this->storeRepository->get($storeId);

        return $store;
    }


    /**
     * {@inheritdoc}
     */
    public function getWebsite($websiteId = null)
    {
        if ($websiteId === null || $websiteId === '') {
            $website = $this->websiteRepository->getById($this->getStore()->getWebsiteId());
        } elseif ($websiteId instanceof Website) {
            $website = $websiteId;
        } elseif ($websiteId === true) {
            $website = $this->websiteRepository->getDefault();
        } elseif (is_numeric($websiteId)) {
            $website = $this->websiteRepository->getById($websiteId);
        } else {
            //$website = $this->websiteRepository->get($websiteId);
            $website = $this->websiteRepository->getDefault();
        }

        return $website;
    }
}
