<?php

error_reporting(E_ALL);

require_once("nusoap.php");
require_once("class.wsdlcache.php");

define('VERSION','2011-08-01');
define('WSDL', 'http://webservices.amazon.com/AWSECommerceService/'.VERSION.'/US/AWSECommerceService.wsdl');
define('DEFAULT_SEARCH', 3);
define('ACCESS_KEY_ID', 'XXXXXXXXXXXXXXXXXX');
define('SECRET_ACCESS_KEY', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
define('ASSOCIATES_ID','ws');
define('MAXTITLELEN', 20);
define('MAXITEMS_TO_DISPLAY', 13);
define('CACHE_PATH','/cache/');
define('CACHE_FILEPREFIX', 'psense_');
define('CACHE_REFRESH', '1'); // Hours before cache becomes stale
define('WSDL_CACHE_DIR', './storage');  // Directory to put cached files
define('WSDL_CACHE_TIME', 0);       // Time to cache in seconds. 0 is unlimited


// Get the token passed in the URL
$i=isset($_GET['i']) ? $_GET['i'] : DEFAULT_SEARCH ;

// Check cache
$cachefile=getcwd().CACHE_PATH.CACHE_FILEPREFIX.$i.'.txt';
if (file_exists($cachefile)) {
    $modtime=filemtime($cachefile);
    if ((time() - $modtime) < CACHE_REFRESH*60*60) {
        $data=file_get_contents($cachefile);
        echo 'document.write(\''.$data.'\');';
        exit;
    }
    unlink($cachefile);
}

// Array of Amazon 'image not found' images
$emptyimage = array(
'Video' => 'http://g-images.amazon.com/images/G/01/video/icons/video-no-image.gif',
'Books' => 'http://g-images.amazon.com/images/G/01/books/icons/books-no-image.gif',
'Kitchen' => 'http://g-images.amazon.com/images/G/01/kitchen/placeholder-icon.gif',
'Jewelry' => 'http://g-images.amazon.com/images/G/01/jewelry/nav/jewelry-icon-no-image-avail.gif',
'Apparel' => 'http://g-images.amazon.com/images/G/01/apparel/general/apparel-no-image.gif',
'GourmetFood' => 'http://g-images.amazon.com/images/G/01/gourmet/gourmet-no-image.gif'
);

// Search Indexes and either relevant browse node or keyword search string
$searchterms = array(
array('Books', '4269'),     // Book reviews page
array('Kitchen', '289939'),  // Rice information page, display rice cookers
array('Video',  'sushi'),     // Video reviews
array('GourmetFood', 'sushi'),  // Cooking page
array('Apparel', 'sushi'),  // News page 1
array('Jewelry' , 'sushi') // News page 2
);

// Do a BrowseNode search if it's a number, a Keywords search otherwise
if (is_numeric($searchterms[$i][1])) {
    $search='BrowseNode';
} else {
    $search='Keywords';
}

// Create a new instance of the caching class
$cache = new wsdlcache(WSDL_CACHE_DIR, WSDL_CACHE_TIME);

// Get the cached WSDL, if it exists
$wsdl = $cache->get(WSDL);

// If WSDL is not cached, cache it
if (is_null($wsdl)) {
    $wsdl = new wsdl(WSDL);
    $cache->put($wsdl);
}

$client = new nusoap_client($wsdl, true);

$err = $client->getError();
if ($err) {
    echo 'WSDL Error: ' . $err .'<br />';
    die();
}

// The method we are using
$method='ItemSearch';

$timestamp = gmdate("Y-m-d\TH:i:s\Z"); 

$method_and_time = $method.$timestamp;

$signature = base64_encode(hash_hmac("sha256",$method_and_time,SECRET_ACCESS_KEY,TRUE));

$headers = array(); 
$headers[] = new soapval( 'AWSAccessKeyId', false, ACCESS_KEY_ID, 'http://security.amazonaws.com/doc/2007-01-01/' );
$headers[] = new soapval( 'Timestamp', false, $timestamp, 'http://security.amazonaws.com/doc/2007-01-01/'  ); 
$headers[] = new soapval( 'Signature', false, $signature, 'http://security.amazonaws.com/doc/2007-01-01/' );  

$client->setHeaders($headers);

/* $params = array( 'AWSAccessKeyId' => ACCESS_KEY_ID, 'AssociateTag' => 'ws', 
'Request' => array( array( 'ItemPage' => '1' ), array( 'ItemPage' => '2' )), 
'Shared' => array ( 'MerchantId' => 'All', $search => $searchterms[$i][1], 
'SearchIndex' => $searchterms[$i][0], 'ResponseGroup' => array ( 'Variations', 'Medium' )));

*/

$params = array( 'Request' => array( array( 'ItemPage' => '1' ), array( 'ItemPage' => '2' )), 
'Shared' => array ( 'MerchantId' => 'All', $search => $searchterms[$i][1], 'SearchIndex' => $searchterms[$i][0], 
'ResponseGroup' => array ( 'Variations', 'Medium' )), 'AWSAccessKeyId' => ACCESS_KEY_ID, 'AssociateTag' => 'ws' );

// Make sure encoding is UTF-8 -- nusoap has no constructor for this
$client->soap_defencoding = 'UTF-8';
$Result = $client->call('ItemSearch', array('body'=>$params));
if ($client->fault) {
    echo '<h2>SOAP Fault</h2>';
    echo "Fault: "; print_r($Result);
    die('fault');
}

$err=$client->getError();
if ($err) {
    echo '<h2>Error</h2>';
    echo "Error: ". $err . '<br />';
    die('call error');
}

/*

echo '<h2>Request</h2>';
echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
echo '<h2>Response</h2>';
echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
echo '<h2>Debug</h2>';
echo '<pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
*/


// Check for Amazon error or no data returned
if ((!isset($Result['Items'])) or (isset($Result['Items'][0]['Request']['Errors']['Error'][0]['Code'])))  {
    $data='Amazon is unavailable right now.<br /> Try again later.';
    echo 'document.write(\''.$data.'\');';
    exit;
}

// Create the outside of the output table
$data='<table bgcolor="ECF8FF"><tr><div style="text-align:center">Product Sense</div></tr>';

$items_processed=1;
// Loop through the list of products returned by Amazon
foreach ($Result['Items'] as $Items) {

    // Stop processing if second batch request has not items
    if (!isset($Items['Item'])) {
        break;
    }
    foreach ($Items['Item'] as $item) {

        // Determine if the ASIN is Parent or Regular, and then determine availability
        if (isset($item['VariationSummary'])) {
            if (isset($item["Variations"]["Item"][0]["Offers"]["Offer"][0]["OfferListing"][0]["SalePrice"]["FormattedPrice"])) {
                $yourprice=$item["Variations"]["Item"][0]["Offers"]["Offer"][0]["OfferListing"][0]["SalePrice"]["FormattedPrice"];
            } elseif (isset($item["Variations"]["Item"][0]["Offers"]["Offer"][0]["OfferListing"][0]["Price"]["FormattedPrice"])) {
                $yourprice=$item["Variations"]["Item"][0]["Offers"]["Offer"][0]["OfferListing"][0]["Price"]["FormattedPrice"];
            } else {
                $yourprice='';
            }
        } else {
            $yourprice=(isset($item["OfferSummary"]["LowestNewPrice"]["FormattedPrice"]))? $item["OfferSummary"]["LowestNewPrice"]["FormattedPrice"] : '';
        }

        // Item is not available for sale in New condition
        if ($yourprice == '') continue;

        $listprice=(isset($item["ItemAttributes"]["ListPrice"]["FormattedPrice"]))? $item["ItemAttributes"]["ListPrice"]["FormattedPrice"] : 'N/A';

        $image = (isset($item["SmallImage"]["URL"])) ? $item["SmallImage"]["URL"] : $emptyimage[$searchterms[$i][0]];

        // Shorten the length of the title, if necessary, to prevent expanding the table cell
        $realtitle=$item['ItemAttributes']['Title'];
        $title = (strlen($realtitle) > MAXTITLELEN) ? substr($realtitle,0, MAXTITLELEN).'...' : $realtitle;

        $detailpage = (isset($item["DetailPageURL"])) ? $item["DetailPageURL"] : '';

        // Add a product to our output table
        $data .= '<tr><td><img src="'.$image.'" alt="'.$realtitle.'" /></td><td><a href="'.$detailpage.'" title="'.$realtitle.'" target="_blank">'. $title .'</a><div>List Price: '. $listprice.'</div><div>Your Price: '.$yourprice.'<br /></div></td></tr>';

        // Number of displayed items controlled by MAXITEMS_TO_DISPLAY
        if ($items_processed++ >= MAXITEMS_TO_DISPLAY) {
            break 2;
        }
    }
}

// Close the table
$data .= '</table>';

// Encode single quotes and remove newlines so Javascript does not break
$data=str_replace("'", "&#039;", $data);
$data=str_replace("\n", "", $data);

// Create new cache file
$cachefile_tmp=$cachefile.getmypid();
$fp=fopen($cachefile_tmp, 'w');
fwrite($fp, $data);
fclose($fp);
@rename($cachefile_tmp, $cachefile);

// Output everything in Javascript format
echo 'document.write(\''.$data.'\');';

?>
