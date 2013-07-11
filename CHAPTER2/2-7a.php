<?php
// Tested with PEAR SOAP version 0.13 and PHP 5.3.6
require_once 'SOAP/Client.php';

die("The operation CustomerContentSearch has been deprecated by Amazon. This code sample no longer works. Sorry.");

// The WSDL URL with date
$wsdl='http://webservices.amazon.com/AWSECommerceService/2011-08-01/US/AWSECommerceService.wsdl';

// Your authentication tokens
$ACCESS_KEY_ID = "XXXXXXXXXXXXXXXXXX";
$SECRET_ACCESS_KEY = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

// The method we are using
$method='CustomerContentSearch';

// Generate a useable timestamp
$timestamp = gmdate("Y-m-d\TH:i:s\Z");

// Concatenate method and timestamp for sig purposes
$method_and_time = $method.$timestamp;

// Generate signature
$signature = base64_encode(hash_hmac("sha256", $method_and_time, $SECRET_ACCESS_KEY, TRUE));

// Create a PEAR SOAP client
$w = new SOAP_WSDL($wsdl);
$client = $w->getProxy();

// Namespace for header elements
$ns =  'http://security.amazonaws.com/doc/2007-01-01/';

// Create necessary SOAP headers
$client->addHeader( new SOAP_Header('AWSAccessKeyId', null, $ACCESS_KEY_ID, 0, array('xmlns' => $ns)) );
$client->addHeader( new SOAP_Header('Timestamp',  null, $timestamp,  0, array('xmlns' => $ns))  );
$client->addHeader( new SOAP_Header('Signature',  null, $signature, 0, array('xmlns' => $ns)) );

$params = array( 'AWSAccessKeyId' => $ACCESS_KEY_ID,
	'AssociateTag' => 'ws',
	'Request' => array ( array (
			'Name' => 'John Smith',
			'ResponseGroup' => array ('CustomerInfo', 'Request')
		)
	)
);

// These options ensure that the request is properly formatted
// Add ('trace' => true) to $options to enable debugging
$options = array( 'use' => 'literal',
	              'keep_arrays_flat' => true );
// Without this curl option, curl fails with an unverified cert error
$client->setopt('curl', CURLOPT_SSL_VERIFYPEER, false);
$Result = $client->call($method, $params, $options);

// getWire() debugging only works if 'trace' is set to true in the options above
// echo $client->getWire();

print_r($Result);

if (PEAR::isError($Result)) {
	echo "<br>An error #" . $Result->getCode() . " occurred!<br>";
	echo " Error: " . $Result->getMessage() . "<br>";
}


?>
