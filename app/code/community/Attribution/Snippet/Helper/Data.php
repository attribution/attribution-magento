<?php

class Attribution_Snippet_Helper_Data extends Mage_Core_Helper_Abstract
{
    const REQUEST_URL_PATH = 'https://track.attributionapp.com/';

    const LOGIN_REQUIRED_AFTER_REGISTER = 'attribution_login_track_required';

    const LOG_FILE_NAME = 'attribution.log';

    /**
     * Send CURL request depending on event type
     * @param $eventType
     * @param $queryString
     * @return bool|string
     */
    public function curlRequest($eventType, $queryString)
    {
        $accountId = $this->getAccountId();
        if (!empty($accountId)) {
            try {
                $url = curl_init(self::REQUEST_URL_PATH . $eventType);
                curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($url, CURLOPT_USERPWD, "$accountId:''");
                curl_setopt($url, CURLOPT_POSTFIELDS, $queryString);
                curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($url, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($queryString))
                );
                $response = curl_exec($url);
                curl_close($url);
                return $response;
            } catch (Exception $exception) {
                Mage::logException($exception);
                return false;
            }
        }
        return false;
    }

    /**
     * Get Attribution account id
     * @return mixed
     */
    public function getAccountId(){
        return Mage::getStoreConfig('attribution_snippet/general/account_id', $this->getCurrentStoreId());
    }

    /**
     * Check if extension is active
     * @return bool
     */
    public function isActive(){
        if (Mage::getStoreConfigFlag('attribution_snippet/general/enable', $this->getCurrentStoreId())) {
            return true;
        }
        return false;
    }

    /**
     * @param $message
     * @param $data
     */
    public function log($message, $data)
    {
        Mage::log($message . " " . $data, null, self::LOG_FILE_NAME);
    }

    public function getCustomerName($customer) {
        $name = trim($customer->getFirstname()) . " " . trim($customer->getLastname());
        return $name;
    }

    public function getCurrentStoreId()
    {
        return Mage::app()->getStore()->getStoreId();
    }

    public function getFormattedCurrentTime($time = null)
    {
        $currentTime = !is_null($time) ? $time : Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s');
        return date("c", strtotime($currentTime));
    }

    public function getCustomerLifetimeStat($customerId)
    {
        try {
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $customerTotals = Mage::getResourceModel('sales/sale_collection')
                ->setOrderStateFilter(Mage_Sales_Model_Order::STATE_CANCELED, true)
                ->setCustomerFilter($customer)
                ->load()
                ->getTotals();
            return $customerTotals;
        } catch (Exception $e){
            return false;
        }
    }

    public function setLoginCookie($value)
    {
        $cookie = Mage::getSingleton('core/cookie');
        $cookie->set(self::LOGIN_REQUIRED_AFTER_REGISTER, $value, 365*86400, '/');
    }

    public function getLoginCookie()
    {
        $cookie = Mage::getSingleton('core/cookie');
        return $cookie->get(self::LOGIN_REQUIRED_AFTER_REGISTER);
    }
}
