<?php

use Edgecom_MarketplaceWebService_Model_Feed_Type as Type;

class Edgecom_MarketplaceWebService_Model_Feed_Generator
{
    /**
     * @var Type
     */
    protected $feed;

    public function __construct(Type $command)
    {
        $this->feed = $command;
    }

    /**
     * Generate an XML file for the requested feed.
     */
    public function execute()
    {
        $this->feed->execute();
    }

    /**
     * Submit the generated XML file to Amazon.
     */
    public function submit()
    {
        /** @var Edgecom_MarketplaceWebService_Helper_Data $helper */
        $helper = Mage::helper('edgecom_marketplacewebservice');

        $awsAccessKeyId = $helper->getAwsAccessKeyId();
        $secretKey = $helper->getSecretKey();
        $applicationName = $helper->getApplicationName();
        $applicationVersion = $helper->getApplicationVersion();

        $service = new MarketplaceWebService_Client(
            $awsAccessKeyId,
            $secretKey,
            array(
                'ServiceURL' => 'https://mws.amazonservices.com',
                'ProxyHost' => null,
                'ProxyPort' => -1,
                'MaxErrorRetry' => 3
            ),
            $applicationName,
            $applicationVersion
        );

        $merchantId = $helper->getSellerId();
        $marketplaceIdArray = array('Id' => array($helper->getMarketplaceId()));

        $request = new MarketplaceWebService_Model_SubmitFeedRequest();
        $request->setMerchant($merchantId);
        $request->setMarketplaceIdList($marketplaceIdArray);
        $request->setFeedType($this->feed->getType());
        $request->setPurgeAndReplace(false);

        if ($stream = fopen($this->feed->getLocation(), 'r')) {
            $request->setContentMd5(base64_encode(md5(stream_get_contents($stream), true)));
            rewind($stream);
            $request->setFeedContent($stream);
            rewind($stream);
        }

        $response = $service->submitFeed($request);

        fclose($stream);

        $feedSubmissionId = $response->getSubmitFeedResult()->getFeedSubmissionInfo()->getFeedSubmissionId();

        Mage::log('Feed #' . $feedSubmissionId . ' has been submitted to Amazon', null, 'amazon.log');
    }
}
