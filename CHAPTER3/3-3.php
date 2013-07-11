<?php

// This file should not be executed directly. It should be launched from the 
// file  launch_productsense.html
// FIX ORDERING FOR USE WITH "DEFAULT SEARCH" of BROWSENODES INSTEAD OF KEYWORDS

error_reporting(E_ALL);
require_once("tools.inc.php");

define('VERSION','2011-08-01');
define('DEFAULT_SEARCH', 4);
define('ACCESS_KEY_ID', 'XXXXXXXXXXXXXXXXXX');
define('SECRET_ACCESS_KEY', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
define('ASSOCIATES_ID','ws');
define('MAXTITLELEN', 20);
define('MAXITEMS_TO_DISPLAY', 10);
define('CACHE_PATH','/cache/');
define('CACHE_FILEPREFIX', 'psense_');
define('CACHE_REFRESH', '1'); // Hours before cache becomes stale

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
'GourmetFood' => 'http://g-images.amazon.com/images/G/01/gourmet/gourmet-no-image. gif'
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
    $search='ItemSearch.Shared.BrowseNode';
} else {
    $search='ItemSearch.Shared.Keywords';
}

$timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z");
$timestamp = str_replace(':','%3A',$timestamp);

// The search request string
$requestparms='AWSAccessKeyId='.ACCESS_KEY_ID.'&AssociateTag='.ASSOCIATES_ID.'&ItemSearch.1.ItemPage=1&ItemSearch.2.ItemPage=2&'.$search.'='. $searchterms[$i][1].'&ItemSearch.Shared.MerchantId=All&ItemSearch.Shared.ResponseGroup=Medium,VariationSummary&ItemSearch.Shared.SearchIndex='.$searchterms[$i][0].'&Operation=ItemSearch&Service=AWSECommerceService&Timestamp='.$timestamp.'&Version='.VERSION;

$request = "GET\necs.amazonaws.com\n/onca/xml\n".$requestparms;

$request = str_replace(':','%3A',$request);
$request = str_replace(';','%3B',$request);
$request = str_replace(',','%2C',$request);

// Signing
$signature = base64_encode(hash_hmac("sha256",$request,SECRET_ACCESS_KEY,TRUE));

$signature = str_replace('+','%2B',$signature);
$signature = str_replace('=','%3D',$signature);
$signature = str_replace('/','%2F',$signature);

$url = 'http://ecs.amazonaws.com/onca/xml?'.$requestparms.'&Signature='.$signature;

// Open the search request and get the response from Amazon
$xml = GetData($url, 5);
// Parse the XML returned by Amazon
$Result = xmlparser($xml);

// Check for Amazon error or no data returned
if (!$Result or (isset($Result['ItemSearchResponse']['Items'][0]['Request']['Errors']['Error'][0]['Code'])))  {
    $data='Amazon is unavailable right now.<br /> Try again later.';
    echo 'document.write(\''.$data.'\');';
    exit;
}

// Create the outside of the output table
$data='<table bgcolor="ECF8FF"><tr><div style="text-align:center">Product Sense</div> </tr>';

$items_processed=1;
// Loop through the list of products returned by Amazon
foreach ($Result['ItemSearchResponse']['Items'] as $Items) {

    // Stop processing if second batch request has no items
    if (!isset($Items['Item'])) {
        break;
    }
    foreach ($Items['Item'] as $item) {

        // Check each data point to make sure it was returned by Amazon
        if (isset($item['VariationSummary'])) {
            $yourprice=(isset($item["VariationSummary"]["LowestPrice"]["FormattedPrice"]))? $item["VariationSummary"]["LowestPrice"]["FormattedPrice"] : '';
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
        $data .= '<tr><td><img src="'.$image.'" alt="'.$realtitle.'" /></td><td><a href="'.$detailpage.'" title="'.$realtitle.'" target="_blank">'. $title .'</a><div> List Price: '. $listprice.'</div><div>Your Price: '.$yourprice.'<br /></div></td></ tr>';

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
