<?php
require_once 'abstract.php';

class Edgecom_Shell_MarketplaceWebService extends Mage_Shell_Abstract
{
    public function run()
    {
        if ($this->getArg('product')) {
            $feed = new Edgecom_MarketplaceWebService_Model_Feed_Type_Product();
        } else if ($this->getArg('relationship')) {
            $feed = new Edgecom_MarketplaceWebService_Model_Feed_Type_Relationship();
        } else if ($this->getArg('inventory')) {
            $feed = new Edgecom_MarketplaceWebService_Model_Feed_Type_Inventory();
        } else if ($this->getArg('price')) {
            $feed = new Edgecom_MarketplaceWebService_Model_Feed_Type_Price();
        } else if ($this->getArg('image')) {
            $feed = new Edgecom_MarketplaceWebService_Model_Feed_Type_Image();
        } else {
            die($this->usageHelp());
        }

        $generator = new Edgecom_MarketplaceWebService_Model_Feed_Generator($feed);
        $generator->execute();
        $generator->submit();
    }

    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f amazon.php -- [options]

  -h            Short alias for help
  help          This help
  image         Upload product images to Marketplace Web Service
  inventory     Synchronise product inventory with Marketplace Web Service
  price         Synchronise product prices with Marketplace Web Service
  product       Synchronise products with Marketplace Web Service
  relationship  Synchronise product relationships, e.g. the configurable / simple product divide

USAGE;
    }
}

$shell = new Edgecom_Shell_MarketplaceWebService();
$shell->run();
