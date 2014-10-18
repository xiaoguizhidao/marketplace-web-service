<?php

interface Edgecom_MarketplaceWebService_Model_Feed_Type
{
    const PRODUCT = '_POST_PRODUCT_DATA_';
    const PRODUCT_RELATIONSHIP = '_POST_PRODUCT_RELATIONSHIP_DATA_';
    const INVENTORY_AVAILABILITY = '_POST_INVENTORY_AVAILABILITY_DATA_';
    const PRODUCT_PRICING = '_POST_PRODUCT_PRICING_DATA_';
    const PRODUCT_IMAGE = '_POST_PRODUCT_IMAGE_DATA_';

    public function execute();

    /**
     * Get the feed location.
     *
     * @return string
     */
    public function getLocation();

    /**
     * Get the feed type.
     *
     * @return string
     */
    public function getType();
}
