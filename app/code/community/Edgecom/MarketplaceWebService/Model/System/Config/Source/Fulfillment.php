<?php

class Edgecom_MarketplaceWebService_Model_System_Config_Source_Fulfillment
{
    public function toOptionArray()
    {
        return array(
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
