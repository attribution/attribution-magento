<?php

class Attribution_Snippet_Model_Observer
{
    /**
     * Send data after customer is registered
     * @param Varien_Event_Observer $observer
     */
    public function customerRegisterSuccess(Varien_Event_Observer $observer) {
        $event = $observer->getEvent();
        $customer = $event->getCustomer();
        if ($queryString = $this->getCustomerData($customer, false)) {
            $eventType = 'identify';
            $result = $this->sendRequest($queryString, $eventType);
            $this->trackLogin($customer->getId(), 'Sign Up');
            $this->setCookie(1);
            Mage::helper('attribution_snippet')->log("Customer register event. Response: ", $result);
        }
    }

    /**
     * Send data after customer is logged in
     * @param Varien_Event_Observer $observer
     */
    public function customerLogout(Varien_Event_Observer $observer) {
       $this->setCookie(1);
    }

    private function setCookie($val)
    {
        Mage::getSingleton('core/cookie')
            ->set(Attribution_Snippet_Helper_Data::LOGIN_REQUIRED_AFTER_REGISTER,
                $val,
                365*86400,
                '/');
    }

    /**
     * Send data after order was created
     * @param Varien_Event_Observer $observer
     */
    public function placeOrder(Varien_Event_Observer $observer) {
        $order = $observer->getEvent()->getOrder();
        if (!empty($order->getId()) && !empty($order->getCustomerId())) {
            $eventType = 'track';
            $data = [
                'user_id' =>  $order->getCustomerId(),
                'event' => "Purchase Complete",
                "properties" => [
                    'revenue' => $order->getGrandTotal(),
                    'number_of_items' => $order->getItemsQty()
                ]
            ];
            $encodedData = json_encode($data);
            $result = $this->sendRequest($encodedData, $eventType);
            Mage::helper('attribution_snippet')->log("Order placed event. Response: ", $result);
        }
    }

    /**
     * Track order return event and send negative revenue
     * @param Varien_Event_Observer $observer
     */
    public function createCreditMemo(Varien_Event_Observer $observer) {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        if (!empty($creditmemo->getId())) {
            $grandTotal = $creditmemo->getGrandTotal();
            $eventType = 'track';
            $data = [
                'user_id' => !empty($creditmemo->getCustomerId()) ? $creditmemo->getCustomerId() : null,
                'event' => "Order refund",
                "properties" => [
                    'revenue' => -1 * ($grandTotal),
                ]
            ];
            $encodedData = json_encode($data);
            $result = $this->sendRequest($encodedData, $eventType);
            Mage::helper('attribution_snippet')->log("Order refund event. Response: ", $result);
        }
    }

    /**
     * @param $data
     * @param $eventType
     * @return mixed
     */
    private function sendRequest($data, $eventType) {
        $result = Mage::helper('attribution_snippet')->curlRequest($eventType, $data);
        return $result;
    }

    /**
     * @param $id
     * @param $eventName
     * @return mixed
     */
    private function trackLogin($id, $eventName) {
        $eventType = 'track';
        $data = [
            'user_id' => $id,
            'event' => $eventName
        ];

        $encodedData = json_encode($data);
        $result = $this->sendRequest($encodedData, $eventType);
        return $result;
    }

    /**
     * Get json string for curl query from customer object
     * @param $customer
     * @param bool $login
     * @return bool|false|string
     */
    private function getCustomerData($customer, $login = true) {
        if (!empty($customer->getId())) {
            $customerId = $customer->getId();
            $name = $this->getCustomerName($customer);
            $data = [
                'user_id' => $customerId,
                 "traits" => [
                    'name' => $name,
                    'email' => $customer->getEmail(),
                 ]
            ];
            if (!$login) {
                $time = $this->getHelper()->getFormattedCurrentTime($customer->getCreatedAt());
                $data['traits']['createdAt'] = $time;
            }
            $queryString = json_encode($data);
            return $queryString;
        }
        return false;
    }

    /**
     * @param $customer
     * @return mixed
     */
    public function getCustomerName($customer) {
        return $this->getHelper()->getCustomerName($customer);
    }

    /**
     * @return Mage_Core_Helper_Abstract
     */
    public function getHelper()
    {
        return Mage::helper('attribution_snippet');
    }
}
