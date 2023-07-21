<?php

namespace Daytours\Wordpress\Block;

use FishPig\WordPress\Block\Context as WPContext;
use \Magento\Framework\View\Element\Template\Context as MagentoContext;
use FishPig\WordPress\Model\ResourceModel\Post\Collection as PostCollection;

class CategoryList extends \FishPig\WordPress\Block\Post{

    /**
     * Cache for post collection
     *
     * @var PostCollection
     */
    protected $_postCollection = null;

    public function __construct(MagentoContext $context, WPContext $wpContext, array $data = [])
    {
        parent::__construct($context, $wpContext, $data);
        $this->setTemplate('list.phtml');
    }

    function getPosts($id_category,$count_item){

        $this->_postCollection = $this->_factory->getFactory('Post')->create()->getCollection();
        $data = $this->_postCollection
            ->addPostTypeFilter('post')
            ->setOrderByPostDate()
            ->addIsViewableFilter()
            ->addCategoryIdFilter($id_category)
            ->setPageSize($count_item)
            ->load();

        return $data;
    }

}