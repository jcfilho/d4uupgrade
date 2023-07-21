<?php
 
namespace Magebay\Bookingsystem\Model;
 
use Magento\Framework\Model\AbstractModel;
 
class Bookingimages extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Magebay\Bookingsystem\Model\ResourceModel\Bookingimages');
    }
	/**
	* get images
	* @params int $dataId, string $dataType
	* @return $items
	**/
	function getBkImages($dataId,$dataType)
	{
		$collection = $this->getCollection()
			->addFieldToFilter('bkimage_type',$dataType)
			->addFieldToFilter('bkimage_data_id',$dataId)
			->setOrder('bkimage_id','DESC');
		return $collection;
	}
	function deleteBkImages($dataId,$dataType)
	{
		$collection = $this->getBkImages($dataId,$dataType);
		if(count($collection))
		{
			foreach($collection as $collect)
			{
				$this->setId($collect->getId())->delete();
			}
		}
	}
}