<?php

namespace Daytours\PartialPayment\Api;

interface PartialPaymentRepositoryInterface
{
    /**
     * @param string $orderId
     * @return array
     */
    public function getOrderById($orderId);

    /**
     * @param string $protectId
     * @return mixed
     */
    public function payDueAmount($protectId);
}
