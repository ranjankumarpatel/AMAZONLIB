<?php 

error_reporting(E_ALL);
require_once("tools.inc.php");

define('ASSOCIATES_ID','ws');            // Associates ID
define('VERSION','2011-08-01');          // ECS Version
define('DEFAULT_BROWSENODE', '672123011'); // The top-level Browse Node ID
define('DEFAULT_NAME', 'Shoes');         // The top-level Browse Category
define('FIRST_TIME', 'FIRST');           // A flag to indicate first time through

$ACCESS_KEY_ID = "XXXXXXXXXXXXXXXXXX";
$SECRET_ACCESS_KEY = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

// Fetch the Browse Node ID
$bn=(isset($_GET['bn'])) ? $_GET['bn'] : FIRST_TIME;

// If it isn't the first time through, make a request
if ($bn != FIRST_TIME) {

$timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z");
$timestamp = str_replace(':','%3A',$timestamp);

$requestparms='AWSAccessKeyId='.$ACCESS_KEY_ID.'&AssociateTag='.ASSOCIATES_ID.'&BrowseNodeId='.$bn.'&Operation=BrowseNodeLookup&Service=AWSECommerceService&Timestamp='.$timestamp.'&Version='.VERSION;
    
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

   $xml = GetData($fullrequest, 10);
    $Result = xmlparser($xml);

}
?>
<html>
<head>
<title>Node Browser</title>
</head>
<body>
<?php
// If first time through, just display the top level Browse Node and Name
if ($bn == FIRST_TIME) {
    echo '<div><a href="'.$_SERVER["PHP_SELF"].'?bn='.DEFAULT_BROWSENODE.'">'.DEFAULT_NAME.'</ a></div>';
}  else {
    // If there are any children, display them
    if (isset($Result['BrowseNodeLookupResponse']['BrowseNodes'][0]['BrowseNode'][0]['Children']['BrowseNode'])) {
        foreach ($Result['BrowseNodeLookupResponse']['BrowseNodes'][0]['BrowseNode'][0]['Children']['BrowseNode'] as $browsenode) {
            echo '<div><a href="'.$_SERVER["PHP_SELF"].'?bn='.$browsenode['BrowseNodeId'][0].'">'. $browsenode['Name'].'</a></div>';
        }
    } else {
        // Otherwise it's a leaf node
        echo '<div>No more child browse nodes</div>';
    }
}
?> 
</body>
</html>
