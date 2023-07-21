<?php

namespace Onetree\ImportDataFixtures\Model\Fixture;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

/**
 * Class CategoryFixture
 * @package Onetree\ImportDataFixtures\Model\Fixture
 */
class CategoryFixture extends AbstractFixture
{
    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    protected $fixtureManager;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\TreeFactory
     */
    protected $resourceCategoryTreeFactory;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * @var \Magento\Framework\Data\Tree\Node
     */
    protected $categoryTree;

    /**
     * @var bool
     */
    protected $mediaInstalled;

    /**
     * CategoryFixture constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\TreeFactory $resourceCategoryTreeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        SampleDataContext $sampleDataContext,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Category\TreeFactory $resourceCategoryTreeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($objectManager);
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->categoryFactory = $categoryFactory;
        $this->resourceCategoryTreeFactory = $resourceCategoryTreeFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param array $row
     * @param \Magento\Catalog\Model\Category $category
     * @return void
     */
    protected function setAdditionalData($row, $category)
    {
        $additionalAttributes = [
            'position',
            'display_mode',
            'page_layout',
            'custom_layout_update',
        ];

        foreach ($additionalAttributes as $categoryAttribute) {
            if (!empty($row[$categoryAttribute])) {
                $attributeData = [$categoryAttribute => $row[$categoryAttribute]];
                $category->addData($attributeData);
            }
        }
    }

    /**
     * Get category name by path
     *
     * @param string $path
     * @return \Magento\Framework\Data\Tree\Node
     */
    protected function getCategoryByPath($path)
    {
        $names = array_filter(explode('/', $path));
        $tree = $this->getTree();
        foreach ($names as $name) {
            $tree = $this->findTreeChild($tree, $name);
            if (!$tree) {
                $tree = $this->findTreeChild($this->getTree(null, true), $name);
            }
            if (!$tree) {
                break;
            }
        }
        return $tree;
    }

    /**
     * Get child categories
     *
     * @param \Magento\Framework\Data\Tree\Node $tree
     * @param string $name
     * @return mixed
     */
    protected function findTreeChild($tree, $name)
    {
        $foundChild = null;
        if ($name) {
            foreach ($tree->getChildren() as $child) {
                if ($child->getName() == $name) {
                    $foundChild = $child;
                    break;
                }
            }
        }
        return $foundChild;
    }

    /**
     * Get category tree
     *
     * @param int|null $rootNode
     * @param bool $reload
     * @return \Magento\Framework\Data\Tree\Node
     */
    protected function getTree($rootNode = null, $reload = false)
    {
        if (!$this->categoryTree || $reload) {
            if ($rootNode === null) {
                $rootNode = $this->storeManager->getDefaultStoreView()->getRootCategoryId();
            }
            $tree = $this->resourceCategoryTreeFactory->create();
            $node = $tree->loadNode($rootNode)->loadChildren();

            $tree->addCollectionData(null, false, $rootNode);

            $this->categoryTree = $node;
        }
        return $this->categoryTree;
    }

    /**
     * @param array $row
     * @return void
     */
    protected function createCategory($row)
    {
        $category = $this->getCategoryByPath($row['path'] . '/' . $row['name']);

        if (!$category) {
            $parentCategory = $this->getCategoryByPath($row['path']);
            $data = [
                'parent_id' => $parentCategory->getId(),
                'name' => $row['name'],
                'is_active' => $row['active'],
                'is_anchor' => $row['is_anchor'],
                'include_in_menu' => $row['include_in_menu'],
                'url_key' => $row['url_key'],
                'id_on_magento1' => $row['id_on_magento1'],
                'category_css_class' => $row['category_css_class'],
                'store_id' => 0
            ];

            $category = $this->categoryFactory->create();
            $category->setData($data)
                ->setPath($parentCategory->getData('path'))
                ->setAttributeSetId($category->getDefaultAttributeSetId());

            $this->setAdditionalData($row, $category);
            $category->save();
        }
    }

    /**
     * the argument represent a row of data
     *
     * @param $data
     * @return bool
     */
    protected function installData($data)
    {
        $this->createCategory($data);
    }
}
