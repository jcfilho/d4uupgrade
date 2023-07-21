<?php
namespace Magebay\Bookingsystem\Plugin\Block\Adminhtml\Order\Create\Items;

class Grid
{
	public function aroundGetConfigureButtonHtml($subject, $procede, $item)
    {
		$product = $item->getProduct();
        $options = ['label' => __('Configure')];
        if ($product->canConfigure() || $product->getTypeId() == 'booking') {
            $options['onclick'] = sprintf('order.showQuoteItemConfiguration(%s)', $item->getId());
        } else {
            $options['class'] = ' disabled';
            $options['title'] = __('This product does not have any configurable options');
        }
        return $subject->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData($options)->toHtml();
    }
}
