<?php

class Edgecom_MarketplaceWebService_Model_Feed_Type_Inventory implements Edgecom_MarketplaceWebService_Model_Feed_Type
{
    protected $handle = null;

    protected $messageId = 1;

    /**
     * Get the feed location.
     *
     * @return string
     */
    public function getLocation()
    {
        return Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . '/feeds/amazon/inventory.xml';
    }

    /**
     * Get the feed type.
     *
     * @return string
     */
    public function getType()
    {
        return self::INVENTORY_AVAILABILITY;
    }

    public function execute()
    {
        /**
         * @var Mage_Catalog_Model_Resource_Product_Collection $products
         */
        $products = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter('type_id', 'configurable')
            ->addAttributeToFilter('status', array('eq' => 1))
            ->addAttributeToSort('name', 'ASC')
        ;

        $this->handle = fopen($this->getLocation(), 'w');

        $sellerId = Mage::helper('edgecom_marketplacewebservice')->getSellerId();

        fputs($this->handle, '<?xml version="1.0" encoding="UTF-8"?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
    <Header>
        <DocumentVersion>1.01</DocumentVersion>
        <MerchantIdentifier>' . $sellerId . '</MerchantIdentifier>
    </Header>
    <MessageType>Inventory</MessageType>
');

        /**
         * @var Mage_Core_Model_Resource_Iterator $iterator
         */
        $iterator = Mage::getSingleton('core/resource_iterator');
        $iterator->walk(
            $products->getSelect(),
            array(array($this, 'addMessage'))
        );

        fputs($this->handle, '</AmazonEnvelope>
');

        fclose($this->handle);
    }

    public function addMessage($args)
    {
        $productId = $args['row']['entity_id'];

        $parentProduct = Mage::getModel('catalog/product')->load($productId);
        $childrenIds = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($productId);
        foreach ($childrenIds[0] as $childrenId) {
            /** @var Mage_Catalog_Model_Product $childProduct */
            $childProduct = Mage::getModel('catalog/product')->load($childrenId);

            $fulfillment = Mage::helper('edgecom_marketplacewebservice')->getFulfillment($childProduct, $parentProduct);
            if ($fulfillment == 'MFN') {
                $qty = '<Quantity>' . (int)$childProduct->getStockItem()->getQty() . '</Quantity>';
            } else {
                $qty = '<Lookup>FulfillmentNetwork</Lookup>';
            }

            fputs($this->handle, '    <Message>
        <MessageID>' . $this->messageId++ . '</MessageID>
        <OperationType>Update</OperationType>
        <Inventory>
            <SKU>' . $childProduct->getSku() . '</SKU>
            <FulfillmentCenterID>' . Mage::helper('edgecom_marketplacewebservice')->getFulfillmentCenter($fulfillment, $childProduct, $parentProduct) . '</FulfillmentCenterID>
            ' . $qty . '
            <SwitchFulfillmentTo>' . $fulfillment . '</SwitchFulfillmentTo>
        </Inventory>
    </Message>
');
        }
    }
}
