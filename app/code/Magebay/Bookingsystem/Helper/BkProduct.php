<?php
 
namespace Magebay\Bookingsystem\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\Category;

class BkProduct extends AbstractHelper
{
	/**
	* @var Magento\Catalog\Model\Category
	**/
	protected $_category;
	protected $category_option = array();
	protected $optionsymbol;
	public function __construct(
       Context $context,
       Category $category
    ) 
	{
       parent::__construct($context);
	   $this->_category = $category;
    }
	function getBookingCategories($parentId,$strAllowCategories)
	{
        $allowCategory = explode(',',$strAllowCategories);
		$categories = $this->_category->getCollection()
                    ->addAttributeToSelect('*')
                    ->addIsActiveFilter()
					->addAttributeToFilter('entity_id',$parentId);
		foreach ($categories as $value) {

			$categoryid = $value->getId();
			if(in_array($categoryid,$allowCategory))
            {
                $this->category_option[$categoryid] = $value->getName();
            }
            //Check has child menu or not
            $hasChild = $this->getChildCategoryCollection($categoryid);
            if(count($hasChild)>0)
            {
                $this->selectRecursiveCategories($categoryid,$allowCategory);
            }

		}
		return $this->category_option;
	}
	public function selectRecursiveCategories($parentID,$allowCategory)
	{
		$childCollection=$this->getChildCategoryCollection($parentID);
		foreach($childCollection as $value){
			$categoryId = $value->getId();
			if(in_array($categoryId,$allowCategory))
            {
                //Check this menu has child or not
                $this->optionsymbol = $this->getCategorySpace($categoryId);
                $this->category_option[$categoryId] = $this->optionsymbol.$value->getName();
                $hasChild=$this->getChildCategoryCollection($categoryId);
                if(count($hasChild)>0)
                {
                    $this->selectRecursiveCategories($categoryId,$allowCategory);
                }
            }
		}
	}
	protected function getCategorySpace($categoryid)
	{
		$path = $this->_category->load($categoryid)->getPath();
		$space="";
		$num = explode("/", $path);
		for($i=1; $i<count($num);$i++)
		{
			$space = $space."-";
		}
		return $space;
	}
	public function getChildCategoryCollection($parentId)
    {
		$categories = $this->_category->getCollection()
			->addAttributeToSelect('*')
            ->addIsActiveFilter()
			->addFieldToFilter("parent_id",$parentId);
    	return $categories;
    }
}