<?php

namespace Daytours\EditOrder\Cron;

class SendReminder
{
    /**
     * @var \Daytours\EditOrder\Helper\OrderSender
     */
    protected $orderSender;

    /**
     *
     *
     * @param \Daytours\EditOrder\Helper\OrderSender $orderSender
     */
    public function __construct(
        \Daytours\EditOrder\Helper\OrderSender $orderSender
    )
    {
        $this->orderSender = $orderSender;
    }

    /**
     * Send remainder emails
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function execute()
    {
        return;
        $this->orderSender->sendMissingOrderDataEmails();
    }
}