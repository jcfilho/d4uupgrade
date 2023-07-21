<?php
/**
 * Created by PhpStorm.
 * User: filho
 * Date: 5/4/18
 * Time: 10:41 AM
 */

namespace Daytours\Catalog\Block;

use Magento\Framework\View\Element\Template;

class Subcategories extends Template
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
        $this->setTemplate('list-subcategories.phtml');
        $this->category = $category;
    }

    public function getSubcategories($idParentCategory){
        $subcategory = $this->category->load($idParentCategory);
        return $subcategory->getChildrenCategories();
    }

}