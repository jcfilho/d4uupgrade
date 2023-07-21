<?php
namespace Magebay\Marketplace\Ui\Component\Listing\Column\Seller\Orders;

class PageActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as & $item) {
                $name = $this->getData("name");
                $id = "X";
                if(isset($item["entity_id"]))
                {
                    $id = $item["entity_id"];
                }
                $item[$name]["view"] = [
                    "href"=>$this->getContext()->getUrl(
                        "marketplace/seller/vieworder",["order_id"=>$id]),
                    "label"=>__("View")
                ];
            }
        }
        return $dataSource;
    }    
}