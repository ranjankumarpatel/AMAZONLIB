<?php

error_reporting(E_ALL);

require_once("tools.inc.php");

define('VERSION','2011-08-01');
define('DEFAULT_SEARCH', 0);
define('ACCESS_KEY_ID', 'XXXXXXXXXXXXXXXXXX');
define('SECRET_ACCESS_KEY', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
define('ASSOCIATES_ID','ws');
define('MAXTITLELEN', 20);
define('MAXITEMS_TO_DISPLAY', 9);
define('CACHE_PATH','/cache/');
define('CACHE_FILEPREFIX', 'psense_');
define('CACHE_REFRESH', '1'); // Hours before cache becomes stale
define('STYLESHEET', 'http://awsbook.com/AMAZONLIB/CHAPTER3/example1.xsl');

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

// Search Indexes and either relevant browse node or keyword search string
$searchterms = array(
array('Books', '4269'),     // Book reviews page
array('Kitchen', '289939'),  // Rice information page, display rice cookers
array('Video',  'sushi'),     // Video reviews
array('GourmetFood', 'sushi'),  // Cooking page
array('Apparel', 'sushi'),  // News page 1
array('Jewelry' , 'sushi') // News page 2
);

$search1='';
$search2='';
// Do a BrowseNode search if it's a number, a Keywords search otherwise
if (is_numeric($searchterms[$i][1])) {
    $search1='&BrowseNode='.$searchterms[$i][1];
} else {
    $search2='&Keywords='.$searchterms[$i][1];
}

$timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z");
$timestamp = str_replace(':','%3A',$timestamp);

// The search request string
$requestparams='AWSAccessKeyId='.ACCESS_KEY_ID.'&AssociateTag='.ASSOCIATES_ID.$search1.'&ContentType=text%2Fhtml&ItemSearch.1.ItemPage=1&ItemSearch.2.ItemPage=2&ItemSearch.Shared.MerchantId=All&ItemSearch.Shared.ResponseGroup=Medium,VariationSummary&ItemSearch.Shared.SearchIndex='.$searchterms[$i][0].$search2.'&Operation=ItemSearch&Service=AWSECommerceService&Style='.STYLESHEET.'&Timestamp='.$timestamp.'&Version='.VERSION.'&maxitems='.MAXITEMS_TO_DISPLAY.'&maxtitlelen='.MAXTITLELEN;

// Make sure stylesheet slashes are encoded
$requestparams = str_replace('/','%2F',$requestparams);

$request = "GET\nxml-us.amznxslt.com\n/onca/xml\n".$requestparams;

$request = str_replace(':','%3A',$request);
$request = str_replace(';','%3B',$request);
$request = str_replace(',','%2C',$request);

// Signing
$signature = base64_encode(hash_hmac("sha256",$request,SECRET_ACCESS_KEY,TRUE));

$signature = str_replace('+','%2B',$signature);
$signature = str_replace('=','%3D',$signature);
$signature = str_replace('/','%2F',$signature);

$fullrequest = 'http://xml-us.amznxslt.com/onca/xml?'.$requestparams.'&Signature='.$signature;

// Open the search request and get the response from Amazon
$Result = GetData($fullrequest, 5);

// Single quotes break Javascript
// Newlines break document.write
$data=str_replace("'", "&#039;", $Result);
$data=str_replace("\n", "", $data);

/* 
// Check for no data returned or Amazon ignores stylesheet and returns XML??
if (!$data or (substr($data, 0, 5) == '<?xml')){
    $data='Amazon is unavailable right now.<br /> Try again later.';
    echo 'document.write(\''.$data.'\');';
    return;
}

// Create new cache file
$cachefile_tmp=$cachefile.getmypid();
$fp=fopen($cachefile_tmp, 'w');
fwrite($fp, $data);
fclose($fp);
@rename($cachefile_tmp, $cachefile);
 */

// Output everything in Javascript format
echo 'document.write(\''.$data.'\');';

?>
