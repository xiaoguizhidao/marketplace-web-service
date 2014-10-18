<?php

class Edgecom_MarketplaceWebService_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_APPLICATION_NAME = 'general/marketplace_web_service/application_name';
    const XML_PATH_APPLICATION_VERSION = 'general/marketplace_web_service/application_version';
    const XML_PATH_SELLER_ID = 'general/marketplace_web_service/seller_id';
    const XML_PATH_MARKETPLACE_ID = 'general/marketplace_web_service/marketplace_id';
    const XML_PATH_AWS_ACCESS_KEY_ID = 'general/marketplace_web_service/aws_access_key_id';
    const XML_PATH_SECRET_KEY = 'general/marketplace_web_service/secret_key';
    const XML_PATH_FULFILLMENT = 'general/marketplace_web_service/fulfillment';
    const XML_PATH_FULFILLMENT_CENTER = 'general/marketplace_web_service/fulfillment_center';

    /**
     * @return string
     */
    public function getApplicationName()
    {
        return Mage::getStoreConfig(self::XML_PATH_APPLICATION_NAME);
    }

    /**
     * @return string
     */
    public function getApplicationVersion()
    {
        return Mage::getStoreConfig(self::XML_PATH_APPLICATION_VERSION);
    }

    /**
     * @return string
     */
    public function getSellerId()
    {
        return Mage::getStoreConfig(self::XML_PATH_SELLER_ID);
    }

    /**
     * @return string
     */
    public function getMarketplaceId()
    {
        return Mage::getStoreConfig(self::XML_PATH_MARKETPLACE_ID);
    }

    /**
     * @return string
     */
    public function getAwsAccessKeyId()
    {
        return Mage::getStoreConfig(self::XML_PATH_AWS_ACCESS_KEY_ID);
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return Mage::getStoreConfig(self::XML_PATH_SECRET_KEY);
    }

    /**
     * @param Mage_Catalog_Model_Product $simple
     * @param Mage_Catalog_Model_Product $configurable
     * @return string
     */
    public function getFulfillment(Mage_Catalog_Model_Product $simple = null, Mage_Catalog_Model_Product $configurable = null)
    {
        if ($simple != null) {
            $fulfillment = $simple->getData('amazon_fulfillment');
        }
        if (empty($fulfillment) && $configurable != null) {
            $fulfillment = $configurable->getData('amazon_fulfillment');
        }
        if (empty($fulfillment)) {
            $fulfillment = Mage::getStoreConfig(self::XML_PATH_FULFILLMENT);
        }
        if (empty($fulfillment)) {
            $fulfillment = 'MFN';
        }
        return $fulfillment;
    }

    /**
     * @param string $fulfillment
     * @param Mage_Catalog_Model_Product $simple
     * @param Mage_Catalog_Model_Product $configurable
     * @return string
     */
    public function getFulfillmentCenter($fulfillment = null, Mage_Catalog_Model_Product $simple = null, Mage_Catalog_Model_Product $configurable = null)
    {
        if (empty($fulfillment)) {
            $fulfillment = $this->getFulfillment();
        }
        switch ($fulfillment) {
            case 'AFN':
                if ($simple != null) {
                    $fulfillmentCenter = $simple->getData('amazon_fulfillment_center');
                }
                if (empty($fulfillmentCenter) && $configurable != null) {
                    $fulfillmentCenter = $configurable->getData('amazon_fulfillment_center');
                }
                if (empty($fulfillmentCenter)) {
                    $fulfillmentCenter = Mage::getStoreConfig(self::XML_PATH_FULFILLMENT_CENTER);
                }
                if (empty($fulfillmentCenter)) {
                    $fulfillmentCenter = 'DEFAULT';
                }
                return $fulfillmentCenter;
            case 'MFN':
            default:
                return 'DEFAULT';
        }
    }
}
