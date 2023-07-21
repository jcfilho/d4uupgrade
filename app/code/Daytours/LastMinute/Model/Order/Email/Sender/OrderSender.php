<?php

namespace Daytours\LastMinute\Model\Order\Email\Sender;

use Magento\Sales\Model\Order;

class OrderSender extends \Magento\Sales\Model\Order\Email\Sender\OrderSender
{

    const LAST_MINUTE_TEMPLATE = 'sales_email_order_lastminute_template';

    /**
     * Prepare email template with variables
     *
     * @param Order $order
     * @return void
     */
    protected function prepareTemplate(Order $order)
    {
        $transport = [
            'order' => $order,
            'billing' => $order->getBillingAddress(),
            'payment_html' => $this->getPaymentHtml($order),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
        ];
        $transport = new \Magento\Framework\DataObject($transport);

        $this->eventManager->dispatch(
            'email_order_set_template_vars_before',
            ['sender' => $this, 'transport' => $transport]
        );

        $this->templateContainer->setTemplateVars($transport->getData());

        $this->_prepareTemplate($order);
    }

    /**
     * Prepare email template taken into account Last Minute case
     *
     * @param Order $order
     * @return void
     */
    protected function _prepareTemplate(Order $order)
    {
        $this->templateContainer->setTemplateOptions($this->getTemplateOptions());

        if ($order->getCustomerIsGuest()) {
            $templateId = $this->identityContainer->getGuestTemplateId();
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $templateId = $order->getIsLastminute() ?
                self::LAST_MINUTE_TEMPLATE : $this->identityContainer->getTemplateId();
            $customerName = $order->getCustomerName();
        }

        $this->identityContainer->setCustomerName($customerName);
        $this->identityContainer->setCustomerEmail($order->getCustomerEmail());
        $this->templateContainer->setTemplateId($templateId);
    }
}
