<?php
require dirname(__FILE__) . '/../app/bootstrap.php';
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
require dirname(__FILE__) . '/abstract.php';

class Getapp extends AbstractApp
{

    const EMAILS_TO_REMOVE = [
        'josuem@onetree.com',
        'josecarlosf+1@onetree.com',
        'josecarlosf+2@onetree.com',
        'josecarlosf+3@onetree.com',
        'josecarlosf+4@onetree.com',
        'josecarlosf+5@onetree.com',
        'josecarlosf+6@onetree.com',
        'josecarlosf+7@onetree.com',
        'josecarlosf+8@onetree.com',
        'josecarlosf+9@onetree.com',
        'josecarlosf+10@onetree.com',
        'josecarlosf+11@onetree.com',
        'josecarlosf+12@onetree.com',
        'josecarlosf+13@onetree.com',
        'josecarlosf+14@onetree.com',
        'josecarlosf+15@onetree.com',
        'josecarlosf+17@onetree.com',
        'josecarlosf+18@onetree.com',
        'josecarlosf+6356@onetree.com',
        'josecarlosf+11@onetree.com',
        'josecarlosf+100@onetree.com',
        'josecarlosf+101@onetree.com',
        'josecarlosf+102@onetree.com',
        'josecarlosf@onetree.com',
        'stefani.viera.qa12@yopmail.com',
        'stefani.viera+1@onetree.com',
        'josecarlos.filhov+103@gmail.com',
        'Stefani.viera+2@onetree.com',
        'stefani.viera.qa15@yopmail.com',
        'josecarlos.filhov@gmail.com',
        'stefani.viera@onetree.com',
        'nohelia@daytours4u.com',
        'stefani.viera+4@onetree.com',
        'cydnqlvidp_1531515195@tfbnw.net',
        'giladshamir@gmail.com',
        'stefani.viera+6@onetree.com',
        'stefani.viera.qa13@yopmail.com',
        'alejandrom@onetee.com',
        'josecarlosf+201@onetree.com',
        'josecarlosf+202@onetree.com',
        'qaqaqa123@yopmail.com',
        'qaqaqaqqa789@yopmail.com',
        'qaqaqaqaqaaq456789@yopmail.com',
        'testing.qa.123@yopmail.com',
        'keilmarojas@gmail.com',
        'christopher@bsas4u.com',
        'vane9141@gmail.com',
        'stefani.viera+5@onetree.com',
        'soldadiego@gmail.com',
        'gilad@daytours4u.com',
        'federico@copetin.com.uy',
        'andre.peixoto@ebanx.com',
        'josecarlosf+11111@onetree.com',
        'stefani.viera+90@onetree.com',
        'stefani.viera+96@onetree.com',
        'gilad@bsass4u.com',
        'daytours_qedldut_fb@tfbnw.net',
        'nohelia.sanchez@gmail.com',
        'rojaskeilma@hotmail.com',
        'alegringus@gmail.com',
        'javitospfv@hotmail.com',
        'juan.carlos.conde.colque@gmail.com',
        'marcjav7@gmail.com',
        'marcjdan7@gmail.com',
        '10217791923813994@facebook.com',
        'alejandrom@onetree.com',
        'gilad@colombia4u.com',
        'gilad@bsas4u.com',
        'nathaly@bsas4u.com',
        'nohelia.shamir@gmail.com',
        'vanessa@bsas4u.com',
    ];

    public function run()
    {
        $state = $this->_objectManager->get('Magento\Framework\App\State');
        $state->setAreaCode('frontend');

        $this->_objectManager->get('Magento\Framework\Registry')
            ->register('isSecureArea', true);


        //Delete all to november, 20

        $quoteCollection = $this->_objectManager->create('\Magento\Quote\Model\ResourceModel\Quote\Collection');
        $quoteCollection->addFieldToFilter('created_at', array('from'=>'2017-01-01 00:00:00', 'to'=>'2018-11-20 00:00:00'));;

        foreach ($quoteCollection as $quoteItem){

            $quoteItemr = $this->_objectManager->create('Magento\Quote\Model\QuoteRepository');
            $quoteItemResult = $quoteItemr->get($quoteItem->getEntityId());
            $quoteItemResult->delete();

            echo $quoteItem->getData('entity_id') . ' -- ' . $quoteItem->getData('customer_email') . '</br>';

        }

        //Delete all by email test
        $quoteCollectionByEmails = $this->_objectManager->create('\Magento\Quote\Model\ResourceModel\Quote\Collection');
        $quoteCollectionByEmails->addFieldToFilter('customer_email', ['in' => self::EMAILS_TO_REMOVE]);

        foreach ($quoteCollectionByEmails as $quoteItem){

            $quoteItemr = $this->_objectManager->create('Magento\Quote\Model\QuoteRepository');
            $quoteItemResult = $quoteItemr->get($quoteItem->getEntityId());
            $quoteItemResult->delete();
            echo $quoteItem->getData('entity_id') . ' -- ' . $quoteItem->getData('customer_email') . '</br>';

        }

    }
}

/** @var \Magento\Framework\App\Http $app */
$app = $bootstrap->createApplication('Getapp');
$bootstrap->run($app);