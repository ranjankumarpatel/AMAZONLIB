<?php 

// Only 10 pages of results allowed now!  :-(

error_reporting(E_ALL);

require_once('tools.inc.php');

define('ASSOCIATES_ID','ws');
define('VERSION','2011-08-01');
define ('NOIMAGE_MED', 'http://g-images.amazon.com/images/G/01/x-site/icons/no-img-lg.gif');
define('DEFAULT_PAGE', '1');

$ACCESS_KEY_ID = "XXXXXXXXXXXXXXXXXX";
$SECRET_ACCESS_KEY = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

// The page number (for either Parent or Child ASINs)
$page=(isset($_GET['page'])) ? $_GET['page'] : DEFAULT_PAGE;

// The Parent ASIN to find details about
$asin=(isset($_GET['asin'])) ? $_GET['asin'] : null;

// The browse node, brand, and keywords search parameters
$bn='672123011';
$brand='Converse';
$keywords='All Star';

$timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z");
$timestamp = str_replace(':','%3A',$timestamp);

// Find Parent ASINs
if (is_null($asin)) {

$requestparms='AWSAccessKeyId='.$ACCESS_KEY_ID.'&AssociateTag='.ASSOCIATES_ID.'&Brand='.$brand.'&BrowseNode='.$bn.'&ItemPage='.$page.'&Keywords='.urlencode($keywords).'&MerchantId=All&Operation=ItemSearch&ResponseGroup=VariationSummary,Images,Small&SearchIndex=Apparel&Service=AWSECommerceService&Timestamp='.$timestamp.'&Version='.VERSION;

    // Find Child ASINS for the given Parent ASIN
} else {

$requestparms='AWSAccessKeyId='.$ACCESS_KEY_ID.'&AssociateTag='.ASSOCIATES_ID.'&ItemId='.$asin.'&MerchantId=All&Operation=ItemLookup&ResponseGroup=Variations,Images,Small&Service=AWSECommerceService&Timestamp='.$timestamp.'&VariationPage='.$page.'&Version='.VERSION;

}

// This is needed for '+' added for space character in request
$requestparms = str_replace('+','%2B',$requestparms);

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

// Make the request
$xml = GetData($fullrequest, 60);

// Parse the results
$Result = xmlparser($xml);

// Generate the output
require_once("4-2layout.php");

// Generate the main display
function theProductWindow() {
    global $Result;

    echo '<table cellspacing="2" cellpadding="2"><tr>';
    $rowcount=0;

    // Go through all the Parent ASINs
    foreach ($Result['ItemSearchResponse']['Items'][0]['Item'] as $item) {

        // Fetch the image
        if (isset($item['MediumImage']['URL'])) {
            $image = $item['MediumImage']['URL'];
        } else {
            $image = NOIMAGE_MED;
        }

        // Get the lowest price
        $lowprice = (isset($item['VariationSummary']['LowestSalePrice']['FormattedPrice'])) ? $item['VariationSummary']['LowestSalePrice']['FormattedPrice'] : ((isset($item['VariationSummary']['LowestPrice']['FormattedPrice'])) ? $item['VariationSummary']['LowestPrice']['FormattedPrice'] : null);

        // Get the highest price
        $highprice = (isset($item['VariationSummary']['HighestSalePrice']['FormattedPrice'])) ? $item['VariationSummary']['HighestSalePrice']['FormattedPrice'] : ((isset($item['VariationSummary']['HighestPrice']['FormattedPrice'])) ? $item['VariationSummary']['HighestPrice']['FormattedPrice'] : null);

        // Ignore this entry if neither price exists
        if (is_null($lowprice) and is_null($highprice)) {
            continue;
        }  elseif (!is_null($lowprice) and is_null($highprice)) {
            $price = $lowprice.' - '.$highprice;
        }  else {
            $price = is_null($lowprice) ? $highprice : $lowprice;
        }

        // Build an output string with the title
        $title = '<b>'.$item["ItemAttributes"]["Title"].'</b><br /><a href="'.$_SERVER["PHP_SELF"].'?asin='.$item["ASIN"][0].'" target="_blank">Price: '.$price.' in various sizes/colors</a>';

        // Only display five items per row
        if (is_int($rowcount/5)) echo '</tr><tr>';

        // Output an item
        echo '<td><div style="text-align: center;">'.$title.'</div><div style="text-align: center;"><img src="'.$image.'" </img></div></td>';
        $rowcount++;
    }
    echo '</tr></table>';
}

// Display the variations window
function theVariationWindow() {
    global $Result;
    global $asin;

    echo '<table cellspacing="2" cellpadding="2"><tr>';

    // Store the Parent ASIN image for later use
    if (isset($Result['ItemLookupResponse']['Items'][0]['Item'][0]['MediumImage']['URL'])) {
        $oldimage = $Result['ItemLookupResponse']['Items'][0]['Item'][0]['MediumImage']['URL'];
    } else {
        $oldimage = NOIMAGE_MED;
    }

    $rowcount=0;

    // Go through the items
    foreach ($Result['ItemLookupResponse']['Items'][0]['Item'][0]['Variations']['Item'] as $item) {

        // Save the title
        $title = $item['ItemAttributes']['Title'];

        // Ignore this item if there's no Offer Listing Id
        if (!isset($item['Offers']['Offer'][0]['OfferListing'][0]['OfferListingId'])) continue;
        $offerlistingid=$item['Offers']['Offer'][0]['OfferListing'][0]['OfferListingId'];

        // Get the price
        $price= (isset($item['Offers']['Offer'][0]['OfferListing'][0]['SalePrice']['FormattedPrice'])) ? $item['Offers']['Offer'][0]['OfferListing'][0]['SalePrice']['FormattedPrice'] : $item['Offers']['Offer'][0]['OfferListing'][0]['Price']['FormattedPrice'] ;

        // Get the availability
        $availability = (isset($item['Offers']['Offer'][0]['OfferListing'][0]['Availability'])) ? $item['Offers']['Offer'][0]['OfferListing'][0]['Availability'] : '' ;

        // Create an immediate buy button using the Offer Listing Id
        $buybutton='<form method="POST" action="http://www.amazon.com/gp/aws/cart/add.html">
<input type="hidden" name="AssociateTag" value="'.ASSOCIATES_ID.'" />
<input type="hidden" name="OfferListingId.1" value="'.$offerlistingid.'" />
<input type="hidden" name="Quantity.1" value="1" />
<input type="submit" name="add" value="Buy Now" />
</form>';

        // Save the size and color
        $size = '<b>Size:</b> '.$item['ItemAttributes']['ClothingSize'];
        $color = '<b>Color:</b> '.$item['ItemAttributes']['Color'];

        // Replace the Parent ASIN with the Child ASIN and test to see if the image exists
        $image = str_replace($asin, $item['ASIN'][0] , $oldimage);
        $imagesize = getimagesize($image);
        if ($imagesize[0] == 1) {
            $image = NOIMAGE_MED;
        }

        // Only display five items per row
        if (is_int($rowcount/5)) echo '</tr><tr>';

        // Output the item
        echo '<td><div style="text-align: center;">'.$title.'</div><div style="text-align: center;">'.$size.'</div><div style="text-align: center;">'.$color.'</div><div style="text-align: center;"><img src="'.$image.'" </img></div><div style="text-align: center;">'.$availability.'</div><div style="text-align: center;">'.$price.'</div><div style="text-align: center;">'.$buybutton.'</div></td>';

        $rowcount++;
    }
    echo '</tr></table>';
}

// Display the list of available pages
function theHeader() {
    global $page;
    global $Result;
    global $asin;

    // Display the Parent ASIN pages
    if (is_null($asin)) {
        $totalpages = (isset($Result['ItemSearchResponse']['Items'][0]['TotalPages'])) ? $Result['ItemSearchResponse']['Items'][0]['TotalPages'] : null;
        if ($totalpages > 10) $totalpages = 10;  // only 10 pages allowed!
    } else {
        // Display the Child ASIN pages
        $totalpages = (isset($Result['ItemLookupResponse']['Items'][0]['Item'][0]['Variations']['TotalVariationPages'])) ? $Result['ItemLookupResponse']['Items'][0]['Item'][0]['Variations']['TotalVariationPages'] : null;
        if ($totalpages > 10) $totalpages = 10;  // only 10 pages allowed!
    }

    $title = (is_null($asin)) ? "Browse Converse All-Stars" : "Browse Specific Shoe Variations";
    echo '<div style="text-align: center;"><h2>'.$title.'</h2></div>';

    // Create a list of pages that the user can choose from
    if (!is_null($totalpages)) {
        echo '<div style="text-align: left;">';
        echo '<form action="'.$_SERVER["PHP_SELF"].'" method="get">More results: page <select name="page">';
        for ($i=1; $i <= $totalpages; $i++)
        {
            $sel= ($i == $page) ? 'selected' : '';
            echo '<option value="'.$i.'"'.$sel.'>'.$i.'</option>';
        }
        echo '</select>';
        if (!is_null($asin)) echo '<input type="hidden" name="asin" value="'.$asin.'" />';
        echo '<input type="submit" value="Go" /></form></div>';
    } else {
        '<div style="text-align: left;"><b>All results are displayed</b></div>';
    }
}

?>
