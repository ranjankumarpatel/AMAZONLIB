<?php

error_reporting(E_ALL);

die("This code sample will no longer look because the ListLookup operation has been deprecated by Amazon.");

require_once("tools.inc.php");

define('VERSION','2011-08-01');
define('ASSOCIATES_ID','ws');
define('DEFAULT_PAGE', 1);
define('MAXNAMELEN', 12);
define('DEFAULT_SORT', 'Price');
define('EMPTY_IMAGE', 'http://g-images.amazon.com/images/G/01/x-site/icons/no-img-sm.gif');
define('CACHE_PATH','/cache/');
define('CACHE_FILEPREFIX', 'wish_');
define('CACHE_REFRESH', '1'); // Hours before cache becomes stale

$ACCESS_KEY_ID = "XXXXXXXXXXXXXXXXXX";
$SECRET_ACCESS_KEY = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

$page=isset($_GET['page']) ? $_GET['page'] : DEFAULT_PAGE ;
$sort=isset($_GET['sort']) ? $_GET['sort'] : DEFAULT_SORT ;
// $id = $_GET['id'];

$id ='2ED2ZE7OME6Y8';

// Check cache
$cachefile=getcwd().CACHE_PATH.CACHE_FILEPREFIX.$id.$sort.$page.'.txt';
if (file_exists($cachefile)) {
    $modtime=filemtime($cachefile);
    if ((time() - $modtime) < CACHE_REFRESH*60*60) {
        $data=file_get_contents($cachefile);
        echo $data;
        exit;
    }
    unlink($cachefile);
}

$nextpage=$page+1;

$timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z");
$timestamp = str_replace(':','%3A',$timestamp);

$requestparms = 'AWSAccessKeyId='.$ACCESS_KEY_ID.'&AssociateTag='.ASSOCIATES_ID.'&ListLookup.1.ProductPage='.$page.'&ListLookup.2.ProductPage='.$nextpage.'&ListLookup.Shared.ListId='.$id.'&ListLookup.Shared.ListType=WishList&ListLookup.Shared.MerchantId=All&ListLookup.Shared.ResponseGroup=ListFull,Medium,Offers&ListLookup.Shared.Sort='.$sort.'&Operation=ListLookup&Service=AWSECommerceService&Timestamp='.$timestamp.'&Version='.VERSION;

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

echo "URL=$fullrequest"; die();
$xml = GetData($fullrequest, 5);
$Result = xmlparser($xml);

// echo "<pre>"; print_r($xml); echo "</pre>"; die();

// Check for Amazon error or no data returned
if (!$Result or (isset($Result['ItemLookupResponse']['Items'][0]['Request']['Errors']['Error'][0]['Code'])))  {
    echo 'Amazon is unavailable right now.<br /> Try again later.';
    exit;
}

$data = '<html><head><link href="wishlist.css" rel="stylesheet" type="text/css"><META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8"> </head><body style="background-color:ECF8FF"><table>';

$totalpages = (int)($Result['ListLookupResponse']['Lists'][0]['List'][0]['TotalPages']/2);
$previouspage = (($page - 2) < 0) ? $totalpages : $page - 1;
$nextpage = (($page + 1) > $totalpages) ? 1 : $page + 1;

if ($Result['ListLookupResponse']['Lists'][0]['List'][0]['TotalPages'] > 2) {
    $data .= '<tr><td colspan="2"><div class="pagination"><a href="'.$_SERVER["PHP_SELF"].'?page='.$previouspage.'&sort='.$sort.'&id='.$id.'" target="_self">&#60;&#60;</a> Page '.$page.' of '.$totalpages.' <a href="'.$_SERVER["PHP_SELF"].'?page='.$nextpage.'&sort='.$sort.'&id='.$id.'" target="_self">&#62;&#62;</a></div></td></tr>';
}

foreach ($Result['ListLookupResponse']['Lists'] as $L) {
    if (!isset($L['List'][0]['ListItem'])) continue;
    foreach ($L['List'][0]['ListItem'] as $list) {
      if (!isset($list['Item'])) continue; // This happens when the desired item has been purchased for the wishlist owner

        $image = (isset($list['Item'][0]['SmallImage']['URL'])) ? $list['Item'][0]['SmallImage']['URL'] : EMPTY_IMAGE ;

        $realtitle = $list['Item'][0]['ItemAttributes']['Title'];
        $itemurl = $list['Item'][0]['DetailPageURL'];
        $title = (strlen($realtitle) > MAXNAMELEN) ? substr($realtitle, 0, MAXNAMELEN).'...' : $realtitle;

        // If there's an offer, it should be the Amazon offer
        $price = (isset($list['Item'][0]['Offers']['Offer'][0]['OfferListing'][0]['Price']['FormattedPrice'])) ? $list['Item'][0]['Offers']['Offer'][0]['OfferListing'][0]['Price']['FormattedPrice'] : '$check Amazon';

        $pg = (isset($list['Item'][0]['ItemAttributes']['ProductGroup'])) ? $list['Item'][0]['ItemAttributes']['ProductGroup'] : '';

        $comment = (isset($list['Comment'])) ? ' Comment: '.$list['Comment'] : '';
        $status = 'Added on: '.$list['DateAdded'].$comment;

        $data .= '<tr><td><img title="'.$status.'" src="'.$image.'" /></td><td><a title="'.$realtitle.'" href="'.$itemurl.'" class="boxname" target="_blank">'.$title.'</a><div class="boxprice">'.$price.'</div><div class="boxproduct">'.$pg.'</div></td></tr>';

    }
}
$data .= "</table></body></html>";

// Create new cache file
$cachefile_tmp=$cachefile.getmypid();
$fp=fopen($cachefile_tmp, 'w');
fwrite($fp, $data);
fclose($fp);
@rename($cachefile_tmp, $cachefile);

echo $data;


?>
