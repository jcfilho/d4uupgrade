<?php

namespace Daytours\Wordpress\Block;



use FishPig\WordPress\Model\ResourceModel\Post\Collection;
use Magento\Framework\View\Element\Template;

class CategoryListFooterBlog  extends Template
{


    private Collection $collection;

    public function __construct(
        Collection $collection,
        Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->collection = $collection;
    }

    function getPosts($id_category, $count_item) {
        $postCollection = $this->collection->addPostTypeFilter('post')
            ->addCategoryIdFilter($id_category)
            ->setOrderByPostDate()
//            ->addIsViewableFilter()
            ->setPageSize($count_item)
            ->load();


//        $postCollection->addPostTypeFilter(['post']);
//        $postCollection->addTermIdFilter($id_category, 'category');
//        $postCollection->setPageSize($count_item);

        return $postCollection;
    }
}