<?php

namespace Onetree\SetupTheme\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;


/**
 * Class Category
 * @package Onetree\SetupTheme\Model
 */
class Category
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
     * @var SampleDataContext
     */
    private $sampleDataContext;
    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    private $websiteFactory;
    /**
     * @var \Magento\Store\Model\ResourceModel\Website
     */
    private $websiteResourceModel;
    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    private $categoryRepository;

    /**
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param \Magento\Catalog\Model\ResourceModel\Category\TreeFactory $resourceCategoryTreeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Store\Model\ResourceModel\Website $websiteResourceModel
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Catalog\Model\ResourceModel\Category\TreeFactory $resourceCategoryTreeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Store\Model\ResourceModel\Website $websiteResourceModel
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->categoryFactory = $categoryFactory;
        $this->resourceCategoryTreeFactory = $resourceCategoryTreeFactory;
        $this->storeManager = $storeManager;
        $this->sampleDataContext = $sampleDataContext;
        $this->websiteFactory = $websiteFactory;
        $this->websiteResourceModel = $websiteResourceModel;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param array $fixtures
     * @throws \Exception
     */
    public function install(array $fixtures)
    {
        foreach ($fixtures as $fileName) {
            $fileName = $this->fixtureManager->getFixture($fileName);
            if (!file_exists($fileName)) {
                continue;
            }
            $rows = $this->csvReader->getData($fileName);
            $header = array_shift($rows);
            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $this->createCategory($data);
            }
        }
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
    protected function getCategoryByPath($path, $rootNode=null)
    {
        $names = array_filter(explode('/', $path));
        $tree = $this->getTree($rootNode);
        foreach ($names as $name) {
            $tree = $this->findTreeChild($tree, $name);
            if (!$tree) {
                $tree = $this->findTreeChild($this->getTree($rootNode, true), $name);
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
        $rootNode = null;
        /** @var  \Magento\Store\Model\Website $website */
        $website = $this->websiteFactory->create();
        $this->websiteResourceModel->load($website, $row['website_code'], 'code');
        if ($website->getId()) {
            $rootNode = $website->getDefaultGroup()->getRootCategoryId();
        }

        $data = array();
        $category = $this->getCategoryByPath($row['path'] . '/' . $row['name'], $rootNode);
        if (!$category) {
            $parentCategory = $this->getCategoryByPath($row['path'], $rootNode);
            
            $data['parent_id'] = $parentCategory->getId();
            $data = array_merge($data, $row);

            /** @var \Magento\Catalog\Model\Category $category */
            $category = $this->categoryFactory->create();
            $category->setData($data)
                ->setPath($parentCategory->getData('path'))
                ->setAttributeSetId($category->getDefaultAttributeSetId());
            $this->setAdditionalData($row, $category);
            $category->save();
        } else {
            /** @var \Magento\Catalog\Model\Category $category */
            $category = $this->categoryRepository->get($category->getId());
            if ($category->getId()) {
                $data = array_merge($data, $row);

                $category->addData($data)
                    ->setPath($category->getParentCategory()->getPath())
                    ->setAttributeSetId($category->getDefaultAttributeSetId());

                $this->categoryRepository->save($category);
            }
        }
    }
}
