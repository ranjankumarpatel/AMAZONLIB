<?php

// This program only works under PHP5

error_reporting(E_ALL);

define('VERSION','2011-08-01');
define('DEFAULT_SEARCH', 0);
define('ACCESS_KEY_ID', 'XXXXXXXXXXXXXXXXXX');
define('SECRET_ACCESS_KEY', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
define('ASSOCIATES_ID','ws');
define('MAXTITLELEN', 20);
define('MAXITEMS_TO_DISPLAY', 15);
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
$requestparms='AWSAccessKeyId='.ACCESS_KEY_ID.'&AssociateTag='.ASSOCIATES_ID.$search1.'&ItemSearch.1.ItemPage=1&ItemSearch.2.ItemPage=2&ItemSearch.Shared.MerchantId=All&ItemSearch.Shared.ResponseGroup=Medium,VariationSummary&ItemSearch.Shared.SearchIndex='.$searchterms[$i][0].$search2.'&Operation=ItemSearch&Service=AWSECommerceService&Timestamp='.$timestamp.'&Version='.VERSION;

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

// Parse the XML returned by Amazon
$Result = simplexml_load_file($url);

/*
echo '<pre>';
print_r($Result);
echo '</pre>';
die();
*/


// Check for Amazon error or no data returned
if ((!isset($Result->OperationRequest)) or (isset($Result->Items->Request->Errors->Error->Code)))  {
    $data='Amazon is unavailable right now.<br /> Try again later.';
    echo 'document.write(\''.$data.'\');';
    return;
}

// Create the outside of the output table
$data='<table bgcolor="ECF8FF"><tr><div style="text-align:center">Product Sense</div></tr>';

$items_processed=1;
// Loop through the list of products returned by Amazon
foreach ($Result->Items as $Items) {
    foreach ($Items->Item as $item) {

        // Check each data point to make sure it was returned by Amazon
        if (isset($item->VariationSummary)) {

            if (isset($item->Variations->Item->Offers->Offer->OfferListing->SalePrice->FormattedPrice)) {
                $yourprice=$item->Variations->Item->Offers->Offer->OfferListing->SalePrice->FormattedPrice;
            } elseif (isset($item->Variations->Item->Offers->Offer->OfferListing->Price->FormattedPrice)) {
                $yourprice=$item->Variations->Item->Offers->Offer->OfferListing->Price->FormattedPrice;
            } else {
                $yourprice='';
            }
        } else {
            $yourprice=(isset($item->OfferSummary->LowestNewPrice->FormattedPrice))? $item->OfferSummary->LowestNewPrice->FormattedPrice : '';
        }

        if ($yourprice == '') continue;

        $listprice=(isset($item->ItemAttributes->ListPrice->FormattedPrice))? $item->ItemAttributes->ListPrice->FormattedPrice : 'N/A';

        $image = (isset($item->SmallImage->URL)) ? $item->SmallImage->URL : $emptyimage[$searchterms[$i][0]];

        // Shorten the length of the title, if necessary, to prevent expanding the table cell
        $realtitle=$item->ItemAttributes->Title;
        $title = (strlen($realtitle) > MAXTITLELEN) ? substr($item->ItemAttributes->Title,0, MAXTITLELEN).'...' : $realtitle;
        $detailpage = (isset($item->DetailPageURL)) ? $item->DetailPageURL : '';

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

