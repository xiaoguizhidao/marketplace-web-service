<?php

class Edgecom_MarketplaceWebService_Model_Feed_Type_Product implements Edgecom_MarketplaceWebService_Model_Feed_Type
{
    protected $handle = null;

    protected $messageId = 1;

    protected $currentName = null;

    /**
     * Get the feed location.
     *
     * @return string
     */
    public function getLocation()
    {
        return Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . '/feeds/amazon/product.xml';
    }

    /**
     * Get the feed type.
     *
     * @return string
     */
    public function getType()
    {
        return self::PRODUCT;
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
    <MessageType>Product</MessageType>
    <PurgeAndReplace>false</PurgeAndReplace>
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

        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product')->load($productId);

        $prefix = $this->getBrand() . ' ' . $this->getGender() . ' ';

        $name = $product->getName();
        $name = $this->ucname($name);
        if (!$this->isCurrentName($name)) {
            fputs($this->handle, '    <Message>
        <MessageID>' . $this->messageId++ . '</MessageID>
        <OperationType>Update</OperationType>
        <Product>
            <SKU>' . $product->getName() . '</SKU>
            <DescriptionData>
                <Title>' . $prefix . $name . '</Title>
                <Brand>' . $this->getBrand() . '</Brand>
                <Description><![CDATA[' . $this->getDescription($product) . ']]></Description>
                ' . $this->getBulletpoints($product) . '
                ' . $this->getPackageDimensions($product) . '
                ' . $this->getSearchTerms($product) . '
                <ItemType>footwear</ItemType>
                ' . $this->getPromotionKeywords($product) . '
            </DescriptionData>
            <ProductData>
                <Shoes>
                    <ClothingType>Shoes</ClothingType>
                    <VariationData>
                        <Parentage>parent</Parentage>
                        <VariationTheme>SizeColor</VariationTheme>
                    </VariationData>
                    <ClassificationData>
                        <Department>Womens</Department>
                        <MaterialType>leather-and-synthetic</MaterialType>
                    </ClassificationData>
                </Shoes>
            </ProductData>
        </Product>
    </Message>
');
        }

        $childrenIds = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($productId);
        foreach ($childrenIds[0] as $childrenId) {
            /** @var Mage_Catalog_Model_Product $childProduct */
            $childProduct = Mage::getModel('catalog/product')->load($childrenId);

            // Only sync products that need to be synced
            if ($childProduct->getData('amazon_sync_date') && mktime($childProduct->getData('amazon_sync_date')) >= mktime($childProduct->getUpdatedAt())) {
                continue;
            }

            try {
                $title = $prefix . $this->ucname($name) . ' ' . $this->getColour($childProduct) . ' ' . $this->getSize($childProduct);

                fputs($this->handle, '    <Message>
        <MessageID>' . $this->messageId++ . '</MessageID>
        <OperationType>Update</OperationType>
        <Product>
            <SKU>' . $childProduct->getSku() .'</SKU>
            <StandardProductID>
                <Type>EAN</Type>
                <Value>' . $childProduct->getSku() .'</Value>
            </StandardProductID>
            <DescriptionData>
                <Title>' . $title . '</Title>
                <Brand>' . $this->getBrand() . '</Brand>
                <Description><![CDATA[' . $this->getDescription($childProduct) . ']]></Description>
                ' . $this->getBulletpoints($childProduct, $product) . '
                ' . $this->getPackageDimensions($childProduct, $product) . '
                <PackageWeight unitOfMeasure="KG">1</PackageWeight>
                <ShippingWeight unitOfMeasure="KG">1</ShippingWeight>
                ' . $this->getSearchTerms($childProduct, $product) . '
                <ItemType>footwear</ItemType>
                ' . $this->getPromotionKeywords($childProduct, $product) . '
            </DescriptionData>
            <ProductData>
                <Shoes>
                    <ClothingType>Shoes</ClothingType>
                    <VariationData>
                        <Parentage>child</Parentage>
                        <Size>' . $childProduct->getAttributeText('shoe_size') . '</Size>
                        <Color>' . $this->getColour($childProduct) . '</Color>
                        <VariationTheme>SizeColor</VariationTheme>
                    </VariationData>
                    <ClassificationData>
                        <Department>Womens</Department>
                    </ClassificationData>
                </Shoes>
            </ProductData>
        </Product>
    </Message>
');

                $childProduct->setData('amazon_sync_date', now());
                $childProduct->save();
            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, 'amazon.log');
                continue;
            }
        }
    }

    public function isCurrentName($name = null)
    {
        if ($this->currentName == $name) {
            return true;
        } else {
            $this->currentName = $name;
            return false;
        }
    }

    /**
     * Get the brand attribute value of a product
     *
     * @return string
     */
    protected function getBrand()
    {
        return 'Brand';
    }

    /**
     * Get the gender attribute value of a product
     *
     * @return string
     */
    protected function getGender()
    {
        return 'Women\'s';
    }

    protected function getDescription($product)
    {
        $description = $product->getData('amazon_description');
        if (!isset($description)) {
            $description = $product->getDescription();
        }
        return $description;
    }

    /**
     * Get the size attribute value of a product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    protected function getSize($product)
    {
        $size = $product->getAttributeText('shoe_size');
        $size = trim($size);
        return $size;
    }

    /**
     * Get the colour attribute value of a product.
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     * @throws Exception
     */
    protected function getColour($product)
    {
        $colour = $product->getColor();
        if ($colour) {
            $colour = $this->ucname($colour);
            $colour = $this->translateColour($colour);
            return $colour;
        }
        throw new Exception("SKU #{$product->getSku()} is missing a colour attribute.");
    }

    /**
     * @param string $colour
     * @return string
     */
    protected function translateColour($colour)
    {
        $colour = str_replace(' /', '/', $colour);
        $colour = str_replace('/ ', '/', $colour);
        $colour = str_replace('/', ' and ', $colour);
        $colour = str_replace(' -', '-', $colour);
        $colour = str_replace('- ', '-', $colour);
        return str_replace('-', ' ', $colour);
    }

    /**
     * Uppercase all words in a string (assuming that spaces, dashes and slashes
     * are all considered separators)
     *
     * @param string $string
     * @return string
     */
    protected function ucname($string)
    {
        $string = trim($string);
        $string = ucwords(strtolower($string));

        foreach (array('-', '/') as $delimiter) {
            if (strpos($string, $delimiter) !== false) {
                $string = implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));
            }
        }
        return $string;
    }

    protected function getBulletpoints($product, $parentProduct = null)
    {
        $bulletpoints = '';
        if (($bulletpoint = $product->getData('amazon_bulletpoint_1'))) {
            $bulletpoints .= $this->addBulletpoint($bulletpoint);
        } else if (isset($parentProduct) && ($bulletpoint = $parentProduct->getData('amazon_bulletpoint_1'))) {
            $bulletpoints .= $this->addBulletpoint($bulletpoint);
        }
        if (($bulletpoint = $product->getData('amazon_bulletpoint_2'))) {
            $bulletpoints .= $this->addBulletpoint($bulletpoint);
        } else if (isset($parentProduct) && ($bulletpoint = $parentProduct->getData('amazon_bulletpoint_2'))) {
            $bulletpoints .= $this->addBulletpoint($bulletpoint);
        }
        if (($bulletpoint = $product->getData('amazon_bulletpoint_3'))) {
            $bulletpoints .= $this->addBulletpoint($bulletpoint);
        } else if (isset($parentProduct) && ($bulletpoint = $parentProduct->getData('amazon_bulletpoint_3'))) {
            $bulletpoints .= $this->addBulletpoint($bulletpoint);
        }
        if (($bulletpoint = $product->getData('amazon_bulletpoint_4'))) {
            $bulletpoints .= $this->addBulletpoint($bulletpoint);
        } else if (isset($parentProduct) && ($bulletpoint = $parentProduct->getData('amazon_bulletpoint_4'))) {
            $bulletpoints .= $this->addBulletpoint($bulletpoint);
        }
        if (($bulletpoint = $product->getData('amazon_bulletpoint_5'))) {
            $bulletpoints .= $this->addBulletpoint($bulletpoint);
        } else if (isset($parentProduct) && ($bulletpoint = $parentProduct->getData('amazon_bulletpoint_5'))) {
            $bulletpoints .= $this->addBulletpoint($bulletpoint);
        }
        return $bulletpoints;
    }

    protected function addBulletpoint($bulletpoint)
    {
        return '<BulletPoint>' . $bulletpoint . '</BulletPoint>';
    }

    protected function getSearchTerms($product, $parentProduct = null)
    {
        $searchTerms = '';
        if (($searchTerm = $product->getData('amazon_searchterm_1'))) {
            $searchTerms .= $this->addSearchTerm($searchTerm);
        } else if (isset($parentProduct) && ($searchTerm = $parentProduct->getData('amazon_searchterm_1'))) {
            $searchTerms .= $this->addSearchTerm($searchTerm);
        }
        if (($searchTerm = $product->getData('amazon_searchterm_2'))) {
            $searchTerms .= $this->addSearchTerm($searchTerm);
        } else if (isset($parentProduct) && ($searchTerm = $parentProduct->getData('amazon_searchterm_2'))) {
            $searchTerms .= $this->addSearchTerm($searchTerm);
        }
        if (($searchTerm = $product->getData('amazon_searchterm_3'))) {
            $searchTerms .= $this->addSearchTerm($searchTerm);
        } else if (isset($parentProduct) && ($searchTerm = $parentProduct->getData('amazon_searchterm_3'))) {
            $searchTerms .= $this->addSearchTerm($searchTerm);
        }
        if (($searchTerm = $product->getData('amazon_searchterm_4'))) {
            $searchTerms .= $this->addSearchTerm($searchTerm);
        } else if (isset($parentProduct) && ($searchTerm = $parentProduct->getData('amazon_searchterm_4'))) {
            $searchTerms .= $this->addSearchTerm($searchTerm);
        }
        if (($searchTerm = $product->getData('amazon_searchterm_5'))) {
            $searchTerms .= $this->addSearchTerm($searchTerm);
        } else if (isset($parentProduct) && ($searchTerm = $parentProduct->getData('amazon_searchterm_5'))) {
            $searchTerms .= $this->addSearchTerm($searchTerm);
        }
        return $searchTerms;
    }

    protected function addSearchTerm($searchTerm)
    {
        return '<SearchTerms>' . $searchTerm . '</SearchTerms>';
    }

    protected function getPromotionKeywords($product, $parentProduct = null)
    {
        $promotionKeywords = '';
        if (($promotionKeyword = $product->getData('amazon_promotionkeyword_1'))) {
            $promotionKeywords .= $this->addPromotionKeyword($promotionKeyword);
        } else if (isset($parentProduct) && ($promotionKeyword = $parentProduct->getData('amazon_promotionkeyword_1'))) {
            $promotionKeywords .= $this->addPromotionKeyword($promotionKeyword);
        }
        if (($promotionKeyword = $product->getData('amazon_promotionkeyword_2'))) {
            $promotionKeywords .= $this->addPromotionKeyword($promotionKeyword);
        } else if (isset($parentProduct) && ($promotionKeyword = $parentProduct->getData('amazon_promotionkeyword_2'))) {
            $promotionKeywords .= $this->addPromotionKeyword($promotionKeyword);
        }
        if (($promotionKeyword = $product->getData('amazon_promotionkeyword_3'))) {
            $promotionKeywords .= $this->addPromotionKeyword($promotionKeyword);
        } else if (isset($parentProduct) && ($promotionKeyword = $parentProduct->getData('amazon_promotionkeyword_3'))) {
            $promotionKeywords .= $this->addPromotionKeyword($promotionKeyword);
        }
        if (($promotionKeyword = $product->getData('amazon_promotionkeyword_4'))) {
            $promotionKeywords .= $this->addPromotionKeyword($promotionKeyword);
        } else if (isset($parentProduct) && ($promotionKeyword = $parentProduct->getData('amazon_promotionkeyword_4'))) {
            $promotionKeywords .= $this->addPromotionKeyword($promotionKeyword);
        }
        if (($promotionKeyword = $product->getData('amazon_promotionkeyword_5'))) {
            $promotionKeywords .= $this->addPromotionKeyword($promotionKeyword);
        } else if (isset($parentProduct) && ($promotionKeyword = $parentProduct->getData('amazon_promotionkeyword_5'))) {
            $promotionKeywords .= $this->addPromotionKeyword($promotionKeyword);
        }
        if (($promotionKeyword = $product->getData('amazon_promotionkeyword_6'))) {
            $promotionKeywords .= $this->addPromotionKeyword($promotionKeyword);
        } else if (isset($parentProduct) && ($promotionKeyword = $parentProduct->getData('amazon_promotionkeyword_6'))) {
            $promotionKeywords .= $this->addPromotionKeyword($promotionKeyword);
        }
        if (($promotionKeyword = $product->getData('amazon_promotionkeyword_7'))) {
            $promotionKeywords .= $this->addPromotionKeyword($promotionKeyword);
        } else if (isset($parentProduct) && ($promotionKeyword = $parentProduct->getData('amazon_promotionkeyword_7'))) {
            $promotionKeywords .= $this->addPromotionKeyword($promotionKeyword);
        }
        if (($promotionKeyword = $product->getData('amazon_promotionkeyword_8'))) {
            $promotionKeywords .= $this->addPromotionKeyword($promotionKeyword);
        } else if (isset($parentProduct) && ($promotionKeyword = $parentProduct->getData('amazon_promotionkeyword_8'))) {
            $promotionKeywords .= $this->addPromotionKeyword($promotionKeyword);
        }
        if (($promotionKeyword = $product->getData('amazon_promotionkeyword_9'))) {
            $promotionKeywords .= $this->addPromotionKeyword($promotionKeyword);
        } else if (isset($parentProduct) && ($promotionKeyword = $parentProduct->getData('amazon_promotionkeyword_9'))) {
            $promotionKeywords .= $this->addPromotionKeyword($promotionKeyword);
        }
        if (($promotionKeyword = $product->getData('amazon_promotionkeyword_10'))) {
            $promotionKeywords .= $this->addPromotionKeyword($promotionKeyword);
        } else if (isset($parentProduct) && ($promotionKeyword = $parentProduct->getData('amazon_promotionkeyword_10'))) {
            $promotionKeywords .= $this->addPromotionKeyword($promotionKeyword);
        }
        return $promotionKeywords;
    }

    protected function addPromotionKeyword($promotionKeyword)
    {
        return '<PromotionKeywords>' . $promotionKeyword . '</PromotionKeywords>';
    }

    protected function getPackageDimensions($product, $parentProduct = null)
    {
        $hasDimensions = false;

        $packageDimensions = "<PackageDimensions>";

        if (($length = $this->getPackageDimensionsLength($product, $parentProduct))) {
            $packageDimensions .= $length;
            $hasDimensions = true;
        }
        if (($width = $this->getPackageDimensionsWidth($product, $parentProduct))) {
            $packageDimensions .= $width;
            $hasDimensions = true;
        }
        if (($height = $this->getPackageDimensionsHeight($product, $parentProduct))) {
            $packageDimensions .= $height;
            $hasDimensions = true;
        }

        $packageDimensions .= '</PackageDimensions>';

        if ($hasDimensions) {
            return $packageDimensions;
        }
        return '';
    }

    protected function getPackageDimensionsLength($product, $parentProduct = null)
    {
        if (($length = $product->getData('shoe_box_dimension_length'))) {
            return '<Length unitOfMeasure="CM">' . $length . '</Length>';
        } else if (isset($parentProduct) && ($length = $parentProduct->getData('shoe_box_dimension_length'))) {
            return '<Length unitOfMeasure="CM">' . $length . '</Length>';
        }
        return false;
    }

    protected function getPackageDimensionsWidth($product, $parentProduct = null)
    {
        if (($width = $product->getData('shoe_box_dimension_width'))) {
            return '<Width unitOfMeasure="CM">' . $width . '</Width>';
        } else if (isset($parentProduct) && ($width = $parentProduct->getData('shoe_box_dimension_width'))) {
            return '<Width unitOfMeasure="CM">' . $width . '</Width>';
        }
        return false;
    }

    protected function getPackageDimensionsHeight($product, $parentProduct = null)
    {
        if (($height = $product->getData('shoe_box_dimension_height'))) {
            return '<Height unitOfMeasure="CM">' . $height . '</Height>';
        } else if (isset($parentProduct) && ($height = $parentProduct->getData('shoe_box_dimension_height'))) {
            return '<Height unitOfMeasure="CM">' . $height . '</Height>';
        }
        return false;
    }
}
