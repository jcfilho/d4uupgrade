<?php
namespace Magebay\Marketplace\Ui\Component\Listing\Column\Seller\Products;

class NameProduct extends \Magento\Ui\Component\Listing\Columns\Column
{	
    public function prepareDataSource(array $dataSource)
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$pro_url_suffix = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('catalog/seo/product_url_suffix');
        if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as & $item) {
                $name = $this->getData("name");
                $id = "X";
                if(isset($item["entity_id"])){
                    $id = $item["entity_id"];
                }
                $item[$name]["view"] = [
                    "href"=> $this->getContext()->getUrl( @$item["url_key"].$pro_url_suffix ),
                    "label"=> @$item["name"]
                ];
            }
        }
        return $dataSource;
    }    
}