<?php

namespace Daytours\Wordpress\Block;

use Magento\Framework\View\Element\Template;

use \FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper;
use \FishPig\WordPress\Model\ResourceModel\Post\Collection as PostCollection;

class RecentArticles extends \FishPig\WordPress\Block\Post
{
    /**
     * Cache for post collection
     *
     * @var PostCollection
     */
    protected $_postCollection = null;

    /*
     * Returns the collection of posts
     *
     * @return
     */
    public function getPosts()
    {
        return $this->_postCollection = $this->_factory->getFactory('Post')->create()
            ->getCollection()
            ->addPostTypeFilter('post')
            ->setOrderByPostDate()
            ->addIsViewableFilter()
            ->setPageSize(3);

    }

}
