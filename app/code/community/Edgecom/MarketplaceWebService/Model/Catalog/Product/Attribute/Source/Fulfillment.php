<?php

class Edgecom_MarketplaceWebService_Model_Catalog_Product_Attribute_Source_Fulfillment extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
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
                'value' => 'AFN',
                'label' => 'Amazon'
            ),
            array(
                'value' => 'MFN',
                'label' => 'Merchant'
            )
        );
    }
}
