<?php


namespace Daytours\Destinations\Model\Config\Source;


use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class DestinationOptions extends AbstractSource
{
    protected $mainDestinations = [53, 184];

    /**
     * @var OptionFactory
     */
    protected $optionFactory;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    public function __construct(
        OptionFactory $optionFactory,
        CategoryFactory $categoryFactory
    )
    {
        $this->optionFactory = $optionFactory;
        $this->categoryFactory = $categoryFactory;
    }

    public function getAllOptions()
    {
        $options = [];

        foreach ($this->mainDestinations as $mainDestination) {
            /**
             * @var Category $destinationCategory
             */
            $destinationCategory = $this->categoryFactory->create()->load($mainDestination);
            $subCategories = $destinationCategory->getCategories($destinationCategory->getId());

            /**
             * @var Category $subCategory
             */
            foreach ($subCategories as $subCategory) {
                $option = $this->optionFactory->create();
                $option->setLabel();
                $option->setValue($subCategory->getId());
                //$options[] = $option;
                $options[] = array(
                    'label' => $destinationCategory->getName() . " | " . $subCategory->getName(),
                    'value' => $subCategory->getId()
                );
            }
        }

        return $options;
    }
}