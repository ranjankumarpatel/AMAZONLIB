<?php
require_once('tools.inc.php');

// List of ASINs (up to 10) to find browse nodes for
$asins='1931859965,1888363827';

// Your authentication tokens
$ACCESS_KEY_ID = "XXXXXXXXXXXXXXXXXX";
$SECRET_ACCESS_KEY = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

$timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z");
$timestamp = str_replace(':','%3A',$timestamp);

// Our Amazon request. Note use of BrowseNodes Response Group
$requestparms='AWSAccessKeyId='.$ACCESS_KEY_ID.'&AssociateTag=ws&ItemId='.$asins.'&MerchantId=All&Operation=ItemLookup&ResponseGroup=BrowseNodes&Service=AWSECommerceService&Timestamp='.$timestamp.'&Version=2011-08-01';

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

//  Fetch and preocess the request
$xml = GetData($fullrequest, 10);
$Result = xmlparser($xml);

// Index to keep track of number of items
$i=0;

// Array of browse nodes in each item
$allnodes=array();

// Loop through items
foreach ($Result['ItemLookupResponse']['Items'][0]['Item'] as $item) {

    // Loop through browse nodes for each item
    foreach ($item['BrowseNodes'][0]['BrowseNode'] as $bnode) {

        // Store browse nodes for each item in the allnodes array
        $allnodes[$i][]=trim($bnode['BrowseNodeId'][0]);

    }
    // Increment index for the next item
    $i++;
}

// Find intersection of browse nodes for all items
$i=0;
$arr1=$allnodes[$i];
$arr2=$allnodes[$i+1];

// Use array_intersect on each set of browse nodes
While (true) {
    $arr2=array_intersect($arr1, $arr2);
    $i++;
    if (!isset($allnodes[$i+1])) break;
    $arr1=$allnodes[$i+1];
}

echo 'Intersection of browse nodes: ';
print_r($arr2);
?>
