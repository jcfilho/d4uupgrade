<?php
/**
 * Created by PhpStorm.
 * User: jose
 * Date: 2/25/19
 * Time: 4:28 PM
 */

namespace Daytours\Ebanx\Override\Gateway\Response\Brazil\CreditCard;


class AuthorizationHandler extends \DigitalHub\Ebanx\Gateway\Response\Brazil\CreditCard\AuthorizationHandler
{

    /**
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = \Magento\Payment\Gateway\Helper\SubjectReader::readPayment($handlingSubject);
        $payment = $payment->getPayment();

        // $this->_logger->info('AuthorizationHandler :: handle');

        $payment_result_data = (array)$response['payment_result'];
        //
        // $this->_logger->info('AuthorizationHandler :: payment result data', $payment_result_data);
        // $this->_logger->info('AuthorizationHandler :: payment result hash', [$payment_result_data['payment']['hash']]);

        $payment->setTransactionId($payment_result_data['payment']['hash']);
        $payment->setAdditionalInformation('transaction_data', $payment_result_data);

        // set transaction not to processing by default wait for notification
        $payment->setIsTransactionPending(false);

        // no not send order confirmation mail
        $payment->getOrder()->setCanSendNewEmailFlag(true);

        // do not close transaction so you can do a cancel() and void
        $payment->setIsTransactionClosed(false);
        $payment->setShouldCloseParentTransaction(false);
    }

}