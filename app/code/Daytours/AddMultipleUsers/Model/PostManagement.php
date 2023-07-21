<?php

namespace Daytours\AddMultipleUsers\Model;

use Exception;
use Magento\Framework\Controller\ResultFactory;

class PostManagement
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    protected $request;

    public function __construct(
        \Magento\Framework\Webapi\Rest\Request $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory

    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getPost()
    {
        $body = $this->request->getBodyParams();
        $results = array();
        foreach ($body["users"] as $user) {
            array_push($results,$this->saveUser($user["email"], $user["password"]));
        }
        return $results;
    }

    private function saveUser($email, $password)
    {
        $result = array(
            "ok" => true,
            "user" => $email,
            "message" => "The account has been added succesfully!"
        );
        try {
            // Get Website ID
            $websiteId  = $this->storeManager->getWebsite()->getId();

            // Instantiate object (this is the most important part)
            $customer   = $this->customerFactory->create();
            $customer->setWebsiteId($websiteId);

            // Preparing data for new customer
            $customer->setEmail($email);
            $customer->setFirstname("-");
            $customer->setLastname("-");
            $customer->setPassword($password);

            // Save data
            $customer->save();
            //$customer->sendNewAccountEmail();
        } catch (Exception $e) {
            $result["ok"] = false;
            $result["message"] = $e->getMessage();
        }

        return $result;
    }
}
