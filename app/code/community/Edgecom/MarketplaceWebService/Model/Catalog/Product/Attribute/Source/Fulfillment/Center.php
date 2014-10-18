<?php

class Edgecom_MarketplaceWebService_Model_Catalog_Product_Attribute_Source_Fulfillment_Center extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        return array(
            array(
                'value' => '',
                'label' => ''
            ),
            array(
                'value' => 'AMAZON_NA',
                'label' => 'North America'
            ),
            array(
                'value' => 'AMAZON_EU',
                'label' => 'North Europe'
            ),
            array(
                'value' => 'AMAZON_JP',
                'label' => 'Japan'
            ),
            array(
                'value' => 'AMAZON_CN',
                'label' => 'China'
            ),
            array(
                'value' => 'AMAZON_IN',
                'label' => 'North India'
            ),
        );
    }
}
