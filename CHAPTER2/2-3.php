<?php
// Your authentication tokens
$ACCESS_KEY_ID = "XXXXXXXXXXXXXXXXXX";
$SECRET_ACCESS_KEY = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

// Generate timestamp
$timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z");
$timestamp = str_replace(':','%3A',$timestamp);

// Order parameters by byte value:  [0-9][A-Z][a-z]
$requestparms = 'AWSAccessKeyId='.$ACCESS_KEY_ID.'&AssociateTag=ws&IdType=ASIN&ItemId=B0001BKAEY&Operation=ItemLookup&ResponseGroup=Request,Small&Service=AWSECommerceService&Timestamp='.$timestamp.'&Version=2011-08-01';

// The request for generating signatures
$request = "GET\necs.amazonaws.com\n/onca/xml\n".$requestparms;

// Make special characters entities
$request = str_replace(':','%3A',$request);
$request = str_replace(';','%3B',$request);
$request = str_replace(',','%2C',$request);

// Generate the signature
$signature = base64_encode(hash_hmac("sha256",$request,$SECRET_ACCESS_KEY,TRUE));

// Make the signature URL safe
$signature = str_replace('+','%2B',$signature);
$signature = str_replace('=','%3D',$signature);
$signature = str_replace('/','%2F',$signature);

// Useable request
$fullrequest = 'http://ecs.amazonaws.com/onca/xml?'.$requestparms.'&Signature='. $signature;

// Fetch the URL into a string
$response=file_get_contents($fullrequest);

// Get listed tag values
$p='/<(IsValid|ItemId|Title|ProductGroup)*>(.*?)<\/\1>/';

// initialize array
$matches=array();

// fill matches array with tags and values
$num = preg_match_all ($p, $response, $matches);

// put array output into a string for easy display
$s=print_r($matches,true);

// print arrays to screen, escaping special characters
echo '<pre>'.htmlspecialchars($s, ENT_QUOTES).'</pre>';
?>  
