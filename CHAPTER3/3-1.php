<?php
require_once('tools.inc.php');

// List of ASINs (up to 10) to find browse nodes for
// Make sure that these are valid ASINs
$asins='B00005UP2P,1931859965';

// Your authentication tokens
$ACCESS_KEY_ID = "XXXXXXXXXXXXXXXXXX";
$SECRET_ACCESS_KEY = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

$timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z");
$timestamp = str_replace(':','%3A',$timestamp);

// Our Amazon request. Note use of BrowseNodes Response Group
$requestparms = 'AWSAccessKeyId='.$ACCESS_KEY_ID.'&AssociateTag=ws&ItemId='.$asins.'&MerchantId=All&Operation=ItemLookup&ResponseGroup=BrowseNodes&Service=AWSECommerceService&Timestamp='.$timestamp.'&Version=2011-08-01';

$request = "GET\necs.amazonaws.com\n/onca/xml\n".$requestparms;

$request = str_replace(':','%3A',$request);
$request = str_replace(';','%3B',$request);
$request = str_replace(',','%2C',$request);

// Signing
$signature = base64_encode(hash_hmac("sha256",$request,$SECRET_ACCESS_KEY,TRUE));

$signature = str_replace('+','%2B',$signature);
$signature = str_replace('=','%3D',$signature);
$signature = str_replace('/','%2F',$signature);

$fullrequest = 'http://ecs.amazonaws.com/onca/xml?'.$requestparms.'&Signature='.$signature;

//  Fetch and process the request
$xml = GetData($fullrequest, 10);
$Result = xmlparser($xml);

// print_r($Result);

// Go through each item listing
foreach ($Result['ItemLookupResponse']['Items'][0]['Item'] as $item) {

    // The ASINs should always be returned, but you check anyway
    $asin= (isset($item['ASIN'][0])) ? $item['ASIN'][0] : ' No ASIN!??';

    echo '<br />ASIN = '.$asin.'<br />';

    // Loop through the browsenodes of each item
    foreach ($item['BrowseNodes'][0]['BrowseNode'] as $bnode) {

        // Check for browse node name
        $name=(isset($bnode['Name']))?$bnode['Name']:'No browse node name';

        echo 'Browse Node = '.$bnode['BrowseNodeId'][0].'<br />';
        echo 'Browse Name = '.$name .'<br />';
        echo '<br />';
    }
}
?>
