<?php 
/**
 * @Author      : haunv
 * @package     Marketplace
 * @copyright   Copyright (c) 2017 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Product;
class Categories extends \Magento\Framework\View\Element\Template
{
     protected $_categoryHelper;
     protected $categoryFlatConfig;
     protected $topMenu;
     protected $_magebayData;
	 protected $_categoryFactory;
     protected $_category;
     protected $_categoryTreeFactory;
     protected $_product;
	 protected $_coreRegistry = null;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,		
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
		\Magebay\Marketplace\Helper\Data $magebayData,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Magento\Catalog\Model\ResourceModel\Category\TreeFactory $categoryTreeFactory,
        \Magento\Theme\Block\Html\Topmenu $topMenu,
		\Magento\Framework\Registry $coreRegistry,
		array $data = []
    ) {
        $this->_categoryHelper = $categoryHelper;
        $this->categoryFlatConfig = $categoryFlatState;
        $this->_magebayData = $magebayData;
        $this->topMenu = $topMenu;
        $this->_categoryTreeFactory = $categoryTreeFactory;
		$this->_categoryFactory = $categoryFactory;	
		$this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }
    /**
     * Return categories helper
     */   
    public function getCategoryHelper()
    {
        return $this->_categoryHelper;
    }
    /**
     * Return top menu html
     * getHtml($outermostClass = '', $childrenWrapClass = '', $limit = 0)
     * example getHtml('level-top', 'submenu', 0)
     */   
    public function getHtml()
    {
        return $this->topMenu->getHtml();
    }
    /**
     * Retrieve current store categories
     *
     * @param bool|string $sorted
     * @param bool $asCollection
     * @param bool $toLoad
     * @return \Magento\Framework\Data\Tree\Node\Collection|\Magento\Catalog\Model\Resource\Category\Collection|array
     */    
   public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        $collection=$this->_categoryHelper->getStoreCategories($sorted , $asCollection, $toLoad);
		return $collection;
    }
    /**
     * Retrieve child store categories
     *
     */ 
    public function getChildCategories($category)
    {
           if ($this->categoryFlatConfig->isFlatEnabled() && $category->getUseFlatResource()) {
                $subcategories = (array)$category->getChildrenNodes();
            } else {
                $subcategories = $category->getChildren();
            }
            return $subcategories;
    }
	/**
     * Get category object
     *
     * @return \Magento\Catalog\Model\Category
     */
	public function getCategory($categoryId) 
	{
		$this->_category = $this->_categoryFactory->create();
		$this->_category->load($categoryId);		
		return $this->_category;
	}	
	
    /**
     * Retrieve children ids comma separated
     *
     * @return string
     */
    public function getChildren($categoryId=false)
    {		
		if ($this->_category) {
			return $this->_category->getChildren();
		} else {
			return $this->getCategory($categoryId)->getChildren();
		}        
    }	
	
	public function getLevelSub(){
		$level=(int)$this->getRequest()->getParam('level')+1;
		return $level;
	}
	
	public function getCategoryById(){	
		$cateId=$this->getCategoryId()?$this->getCategoryId():(int)$this->getRequest()->getParam('categoryid');
		$categorychild_ids=$this->getChildren($cateId);				
		$categorychild_idarray=explode(',',$categorychild_ids);
		$categories=array();
		foreach($categorychild_idarray as $k=>$v){
			$categories[]=$this->getCategory($v);
		}
		return $categories;
	}
	
	public function getProduct(){
		if($this->_coreRegistry->registry('current_product')) {
			return $this->_coreRegistry->registry('current_product');
		} else {
			$_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$_product = $_objectManager->create('Magento\Catalog\Model\Product');
			$productId=(int)$this->getRequest()->getParam('id');
			if($productId) {
				$_product->load($productId);
			}
			return $_product;
		}
	}	
}