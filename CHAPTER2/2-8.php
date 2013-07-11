<?php

// This is just a code fragment -- not a complete script

$ACCESS_KEY_ID = "xxxxxxxxxxxxxxxxx";
$SECRET_ACCESS_KEY = "xxxxxxxxxxxxxxxxxxxxxxx";

$params =  array(
	'CustomerContentSearch' => array( 'AWSAccessKeyId' => $ACCESS_KEY_ID,
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
			array(
				'ItemId' => array('B0001BKAEY'),
				'IdType' => 'ASIN',
				'ResponseGroup' => array('Request', 'Small')
			)
		)
	)
);

?>
