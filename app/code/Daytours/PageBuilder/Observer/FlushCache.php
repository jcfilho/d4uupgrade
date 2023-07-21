<?php

namespace Daytours\PageBuilder\Observer;

use Magento\Framework\Event\ObserverInterface;

class FlushCache implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;

    /**
     * @param \Magento\Framework\App\CacheInterface $cache
     */
    public function __construct(\Magento\Framework\App\CacheInterface $cache)
    {
        $this->_cache = $cache;
    }

    /**
     * Clear Page builder block cache
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_cache->clean([\Ves\PageBuilder\Model\Block::CACHE_TAG]);
    }
}
