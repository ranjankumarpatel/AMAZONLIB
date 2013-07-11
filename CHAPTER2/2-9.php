<?php

// This program only works under PHP5. Tested on PHP 5.3.6

die("The operation CustomerContentSearch has been deprecated by Amazon. This code sample no longer works. Sorry.");


// Your authentication tokens
$ACCESS_KEY_ID = "XXXXXXXXXXXXXXXXXX";
$SECRET_ACCESS_KEY = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

// URL of the Amazon WSDL file
$wsdl='http://webservices.amazon.com/AWSECommerceService/2011-08-01/US/AWSECommerceService.wsdl';

// The Document/Literal format request parameters
$params =  array(
	'CustomerContentSearch' => array(
		'AWSAccessKeyId' => $ACCESS_KEY_ID,
		'AssociateTag' => 'ws',
		'Shared' => array(
			'ResponseGroup' => array ('CustomerInfo', 'Request')
		),
		'Request' => array (
			array( 'Name' => 'Jeff Bezos'),
			array( 'Email' => 'jeffb@amazon.com')
		)
	),
	'ItemLookup' => array(
		'AWSAccessKeyId' => $ACCESS_KEY_ID,
		'AssociateTag' => 'ws',
		'Request' => array (
			array(  'ItemId' => array('B0001BKAEY'),
				'IdType' => 'ASIN',
				'ResponseGroup' => array('Request', 'Small')
			)
		)
	)
);

// The method we are using
$method='MultiOperation';

$timestamp = gmdate("Y-m-d\TH:i:s\Z");

$method_and_time = $method.$timestamp;

$signature = base64_encode(hash_hmac("sha256", $method_and_time, $SECRET_ACCESS_KEY, TRUE));

// Parse the WSDL to get the client methods
$client = new SoapClient($wsdl, array('exceptions' => false, 'soap_version' => SOAP_1_1, 'trace' => true));

// Set headers
$headers = array();
$headers[] = new SoapHeader( 'http://security.amazonaws.com/doc/2007-01-01/', 'AWSAccessKeyId', $ACCESS_KEY_ID );
$headers[] = new SoapHeader( 'http://security.amazonaws.com/doc/2007-01-01/', 'Timestamp', $timestamp );
$headers[] = new SoapHeader( 'http://security.amazonaws.com/doc/2007-01-01/', 'Signature', $signature );
$client->__setSoapHeaders($headers);

// The method we're using
$method='MultiOperation';

// Send the SOAP request to Amazon
$Result = $client->$method($params);

// Output just the request
echo "<pre>".htmlspecialchars($client->__getLastRequest(), ENT_QUOTES)."</pre>";

?>
