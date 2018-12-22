<?php
class Attribution_Snippet_Block_Snippet extends Mage_Core_Block_Template
{
    const ATTRIBUTION_COOKIE_IDENTIFIER = 'attribution_identifier';

    const CHECKOUT_REGISTER_METHOD = 'register';

    /**
     * Get logged in user ID
     * @return bool
     */
    private function getUserId()
    {
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            return $customerData->getId();
        }
        return false;
    }

    /**
     * Check if customer is logged and cookie does not set to trigger identify event
     * Trigger Identify only after customer log in
     * @return bool
     */
    public function checkCookie()
    {
        $cookie = Mage::getSingleton('core/cookie');
        if (empty($cookie->get(self::ATTRIBUTION_COOKIE_IDENTIFIER)) && $this->getUserId()) {
            $cookie->set(self::ATTRIBUTION_COOKIE_IDENTIFIER, $this->getUserId(), 86400, '/');
            return $this->getUserId();
        }
        return false;
    }

    protected function getActionName()
    {
        return [
            'controller' => $this->getRequest()->getControllerName(),
            'action' => $this->getRequest()->getActionName()
        ];
    }

    public function loginEvent()
    {
        $helper = Mage::helper('attribution_snippet');
        $loginEventRequire = false;
       if (Mage::getSingleton('customer/session')->isLoggedIn()) {
           if ($helper->getLoginCookie() == 1) {
               $loginEventRequire = true;
               $helper->setLoginCookie(0);
               Mage::helper('attribution_snippet')->log("Customer login event.", "");
           }
       } else {
           $helper->setLoginCookie(1);
       }
       return $loginEventRequire;
    }

    public function loginIdentifyData()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        return !empty($customer) ? $customer : null;
    }

    public function getOnepageSuccessIdentify()
    {
        $actionName = $this->getActionName();
        if ($actionName['controller'] == 'onepage' && $actionName['action'] == 'success') {
            $order = $this->getOrder();
            if (!empty($order) && $orderId = $order->getId()) {
                return $this->getOrderDetailsById($orderId);
            }
        }
        return false;
    }

    private function getOrderDetailsById($orderId)
    {
        if ($orderId) {
            $order = $this->getOrder($orderId);
            if (!empty($order->getId())) {
                $data = [
                    'user_id' => !empty($order->getCustomerId()) ? $order->getCustomerId() : null,
                    'email' => $order->getCustomerEmail(),
                    'first_name' => $order->getCustomerFirstname(),
                    'last_name' => $order->getCustomerLastname(),
                    'revenue' => $order->getGrandTotal()
                ];
                return $data;
            }
        }
        return false;
    }

    /**
     * @return bool|Mage_Core_Model_Abstract
     */
    public function checkIfRegisteredInCheckout()
    {
        if (!empty($this->getOrder())) {
            try {
                $order = $this->getOrder();
                $quoteId = $order->getQuoteId();
                $quote = Mage::getModel('sales/quote')->load($quoteId);
                $method = $quote->getCheckoutMethod(true);
                if ($method == self::CHECKOUT_REGISTER_METHOD) {
                    return $order;
                }
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * @param $orderId
     * @return bool|Mage_Core_Model_Abstract
     */
    private function getOrder()
    {
        $session=Mage::getSingleton('checkout/type_onepage')->getCheckout();
        $session->getLastSuccessQuoteId();
        $orderId= $session->getLastOrderId();
        if(is_null($orderId)){
            $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        }
        try {
            $order = Mage::getModel('sales/order')->load($orderId);
            return $order;
        } catch (Exception $e) {
            return false;
        }
    }
}
