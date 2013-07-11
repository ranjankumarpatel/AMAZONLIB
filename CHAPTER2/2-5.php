<?php

// PHP 5 SOAP. This program only works under PHP5
// Tested under PHP 5.2.11

// URL of the Amazon WSDL file which includes the version namespace
$wsdl='http://webservices.amazon.com/AWSECommerceService/2011-08-01/US/AWSECommerceService.wsdl';

// Your authentication tokens
$ACCESS_KEY_ID = "XXXXXXXXXXXXXXXXXX";
$SECRET_ACCESS_KEY = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

// The method we are using
$method='ItemLookup';

// A useable timestamp
$timestamp = gmdate("Y-m-d\TH:i:s\Z"); 

// Concatenate the method and timestamp for signature purposes
$method_and_time = $method.$timestamp;

// Generate a signature
$signature = base64_encode(hash_hmac("sha256",$method_and_time,$SECRET_ACCESS_KEY,TRUE));

// The SOAP client
$client = new SoapClient($wsdl);

// Set headers
$headers = array(); 
$headers[] = new SoapHeader( 'http://security.amazonaws.com/doc/2007-01-01/', 'AWSAccessKeyId', $ACCESS_KEY_ID ); 
$headers[] = new SoapHeader( 'http://security.amazonaws.com/doc/2007-01-01/', 'Timestamp', $timestamp ); 
$headers[] = new SoapHeader( 'http://security.amazonaws.com/doc/2007-01-01/', 'Signature', $signature ); 
$client->__setSoapHeaders($headers); 

// The Document/Literal parameter array
$request = array (
'IdType' => 'ASIN',
'ItemId' => array('B0001BKAEY'),
'Operation' => $method,
'ResponseGroup' => array('Request', 'Small')
);

$params=array(
'AWSAccessKeyId' => $ACCESS_KEY_ID,
'AssociateTag' => 'ws',
"Operation" => $method,
'Request' => $request
);

$Result = $client->$method($params);

// Output the results
echo '<pre>';
print_r($Result);
echo '</pre>';
?>
