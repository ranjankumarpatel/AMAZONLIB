<?php

// Tested under PHP 5.3.6 and NuSOAP 0.9.5
require_once("nusoap.php");

die("The operation CustomerContentSearch has been deprecated by Amazon. This code sample no longer works. Sorry.");

// The WSDL USRL with date
$wsdl='http://webservices.amazon.com/AWSECommerceService/2011-08-01/US/AWSECommerceService.wsdl';

// Your authentication tokens
$ACCESS_KEY_ID = "XXXXXXXXXXXXXXXXXX";
$SECRET_ACCESS_KEY = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

// The method we are using
$method='CustomerContentSearch';

// Generate a useable date
$timestamp = gmdate("Y-m-d\TH:i:s\Z"); 

// Concatenate method and timestamp for sig purposes
$method_and_time = $method.$timestamp;

// Generate signature
$signature = base64_encode(hash_hmac("sha256",$method_and_time,$SECRET_ACCESS_KEY,TRUE));

// Create a nusoap client
$client = new nusoap_client($wsdl, true);

// Create necessary SOAP headers
$headers = array(); 
$headers[] = new soapval( 'AWSAccessKeyId', false, $ACCESS_KEY_ID, 'http://security.amazonaws.com/doc/2007-01-01/' );
$headers[] = new soapval( 'Timestamp', false, $timestamp, 'http://security.amazonaws.com/doc/2007-01-01/'  ); 
$headers[] = new soapval( 'Signature', false, $signature, 'http://security.amazonaws.com/doc/2007-01-01/' );  

$client->setHeaders($headers);

$params = array( 'AWSAccessKeyId' => $ACCESS_KEY_ID,
'AssociateTag' => 'ws',
'Request' => array (
array( 'Name' => 'John Smith',
'ResponseGroup' => array ('CustomerInfo', 'Request' )
)
)
);

$client->soap_defencoding = 'UTF-8';
$Result = $client->call('CustomerContentSearch', array('body' => $params));
echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';

// print_r($Result);

unset($client)

?>
