<?php

class Edgecom_MarketplaceWebService_Model_Observer
{
    public function exportProduct()
    {
        $this->export(new Edgecom_MarketplaceWebService_Model_Feed_Type_Product());
    }

    public function exportRelationship()
    {
        $this->export(new Edgecom_MarketplaceWebService_Model_Feed_Type_Relationship());
    }

    public function exportInventory()
    {
        $this->export(new Edgecom_MarketplaceWebService_Model_Feed_Type_Inventory());
    }

    public function exportPrice()
    {
        $this->export(new Edgecom_MarketplaceWebService_Model_Feed_Type_Price());
    }

    public function exportImage()
    {
        $this->export(new Edgecom_MarketplaceWebService_Model_Feed_Type_Image());
    }

    protected function export(Edgecom_MarketplaceWebService_Model_Feed_Type $feedType)
    {
        $generator = new Edgecom_MarketplaceWebService_Model_Feed_Generator($feedType);
        $generator->execute();
        $generator->submit();
    }
}
