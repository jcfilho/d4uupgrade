<?php

namespace Daytours\PageBuilder\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Directory\Model\Currency;
use Magento\Review\Model\Review\SummaryFactory;
use Magebay\Bookingsystem\Model\BookingsFactory;
use Magebay\Bookingsystem\Model\OptionsFactory;
use Magebay\Bookingsystem\Model\OptionsdropdownFactory;
use Magebay\Bookingsystem\Model\DiscountsFactory;
use Magebay\Bookingsystem\Model\FacilitiesFactory;
use Magebay\Bookingsystem\Helper\BkHelperDate;
use Magebay\Bookingsystem\Helper\RentPrice;
use Magebay\Bookingsystem\Helper\BkOrderHelper;
use Magebay\Bookingsystem\Helper\BkSimplePriceHelper;
use Magebay\Bookingsystem\Helper\BkText;
use Magebay\Bookingsystem\Model\Image as ImageModel;

class SearchBlock extends \Magento\Framework\View\Element\Template
{

    const CATEGORY_BY_DEFAULT = 2;

    /**
     * @var Template\Context
     */
    private $context;
    /**
     * @var \Magento\Catalog\Model\Category
     */
    private $category;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonFactory;

    public function __construct(
        Template\Context $context,
        \Magento\Catalog\Model\Category $category,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        array $data = []
    )
    {

        parent::__construct($context, []);
        $this->context = $context;
        $this->category = $category;
        $this->jsonFactory = $jsonFactory;
    }

    public function getDefaultCategory(){
        return self::CATEGORY_BY_DEFAULT;
    }

    public function getCategories($parentId){
        $subcategory = $this->category->load($parentId);
        return $subcategory->getChildrenCategories();
    }

    public function getDataForChildrenCategories($id){
        $resultJson = $this->jsonFactory->create();
        $data = $this->getCategories($id);
        $result = [];
        foreach ($data as $item){
             $result[] = [
                 'id' => $item->getId(),
                 'name' => $item->getName()
             ];
        }
        $response = array('result'=>$result);
        return json_encode($response);

    }

    public function getAllDataCategories(){
        $parentCategories = $this->getCategories(self::CATEGORY_BY_DEFAULT);
        $result = [];
        foreach ($parentCategories as $item){
            $result[] = [
                'id'        =>   $item->getId(),
                'name'      =>   $item->getName(),
                'children'  =>   $this->getDataForChildrenCategories($item->getId())
            ];
        }
        return $result;
    }

}