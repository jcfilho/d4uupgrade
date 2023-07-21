<?php
/**
 * Created by PhpStorm.
 * User: filho
 * Date: 5/4/18
 * Time: 10:41 AM
 */

namespace Daytours\Catalog\Block;

use Magento\Framework\View\Element\Template;

class CategoriesToSearchHome extends Template
{
    /**
     * @var \Magento\Catalog\Model\Category
     */
    private $category;

    public function __construct(
        Template\Context $context,
        array $data = [],
        \Magento\Catalog\Model\Category $category)
    {
        parent::__construct($context, $data);
        $this->setTemplate('list-categories-search-home.phtml');
        $this->category = $category;
    }

    public function getSubcategories($idParentCategory){
        $subcategory = $this->category->load($idParentCategory);
        return $subcategory->getChildrenCategories();
    }

    public function getCategoryParentURL($idParentCategory){
        $category = $this->category->load($idParentCategory);
        return $category->getUrl();
    }

}