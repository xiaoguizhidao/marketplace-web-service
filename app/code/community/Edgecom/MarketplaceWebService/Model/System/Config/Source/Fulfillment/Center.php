<?php

class Edgecom_MarketplaceWebService_Model_System_Config_Source_Fulfillment_Center
{
    public function toOptionArray()
    {
        return array(
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
