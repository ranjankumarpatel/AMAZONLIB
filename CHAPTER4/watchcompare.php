<?php
// Bring in our XML parser
require_once("tools.inc.php");

define('VERSION', '2011-08-01');           // Version of ECS
define('ASSOCIATES_ID','ws');              // Amazon Associates ID
define('DEFAULT_SORT', 'salesrank');       // Default sorting
define('NO_BRAND', '');                    // Defaults for search parameters
define('NO_TYPE', 'all');
define('NO_PRICE', '');
define('NO_MATERIAL', '');
define('NO_IMAGE', 'http://g-images.amazon.com/images/G/01/jewelry/nav/jewelry-icon-no-image-avail.gif');                      // Empty Image
define('SEARCHINDEX', 'Watches');          // The Search Index

$ACCESS_KEY_ID = "XXXXXXXXXXXXXXXXXX";
$SECRET_ACCESS_KEY = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

// Browse Nodes for 'Type' search parameter
$browsenodes=array(
'all' => '1292936011',
'mens' => '379281011',
'womens' => '379282011',
'childrens' => '379283011'
);

//378516011  377110011    404124011    379281011
// If the user hit the 'Compare' button, collect the ASINS they
// want to compare, and put them in the $asins string, separated by commas
$asins='';
$count_asins=0;
if (isset($_POST['compare']) and ($_POST['compare'] == 'Compare')) {
    foreach ($_POST as $key => $value) {
        if ($value == 'asin') {
            $count_asins++;
            $asins .= $key.',';
        }
    }
    $asins=substr($asins, 0, strrpos($asins, ","));
}

// If the user requested a search, collect the search parameters or
// take the defaults
$price=(isset($_POST['price']) && $_POST['price'] != 'all') ? $_POST['price'] : NO_PRICE ;
$brand=(isset($_POST['brand']) && $_POST['brand'] != 'all') ? $_POST['brand'] : NO_BRAND ;
$type=isset($_POST['type']) ? $_POST['type'] : NO_TYPE ;
$material=(isset($_POST['material']) && $_POST['material'] != 'all' ) ? $_POST['material'] : NO_MATERIAL ;
$sort=isset($_POST['sort']) ? $_POST['sort'] : DEFAULT_SORT ;
$browsenode=$browsenodes[$type];

// Functions to make the search form 'sticky'
// The customer's search selections will be maintained after
// they submit the form
function select_type ($t) {
    global $type;
    if ($type == $t) { echo 'selected'; }
}

function select_brand ($t) {
    global $brand;
    if ($brand == $t) { echo 'selected'; }
}

function select_material ($t) {
    global $material;
    if ($material == $t) { echo 'selected'; }
}

function select_price ($t) {
    global $price;
    if ($price == $t) { echo 'selected'; }
}

function select_sort ($t) {
    global $sort;
    if ($sort == $t) { echo 'selected'; }
}

// The min/max price comes in as two numbers separated by a '-',so
// we split it in two here and create a suffix for the request
$parr=explode('-',$price);
$Sprice= ($price == NO_PRICE) ? '' : '&MaximumPrice='. ($parr[1]).'&MinimumPrice='.($parr[0]);

// Create the Brand parameter for the request
$Sbrand= ($brand == NO_BRAND)? '' : '&Brand='.$brand;

// Create the Keywords parameter for the request
$Skeywords= ($material == NO_MATERIAL)? '' : '&Keywords='.urlencode($material);

$timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z");
$timestamp = str_replace(':','%3A',$timestamp);

// If the user selected items to compare, then $asins will be set. Otherwise
// We assume a search has been requested and do a search
if ($asins == '') {
    $task='ItemSearchResponse';
    $requestparms='AWSAccessKeyId='.$ACCESS_KEY_ID.'&AssociateTag='.ASSOCIATES_ID.$Sbrand.'&BrowseNode='.$browsenode.$Skeywords.$Sprice.'&Operation=ItemSearch&ResponseGroup=Medium&SearchIndex='.SEARCHINDEX.'&Service=AWSECommerceService&Sort='.$sort.'&Timestamp='.$timestamp.'&Version='.VERSION;
} else {
    $task='ItemLookupResponse';
    $requestparms='AWSAccessKeyId='.$ACCESS_KEY_ID.'&AssociateTag='.ASSOCIATES_ID.'&ItemId='.$asins.'&Operation=ItemLookup&ResponseGroup=Medium&Service=AWSECommerceService&Timestamp='.$timestamp.'&Version='.VERSION;
}

// After a POST, any spaces are replaced with the '+' character, which we must turn into an entity
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

// Because the user can do so many variations on searches, and I do not
// expect very heavy usage on this application, I opted not to use a cache.
// Instead, if the request fails, I wait two seconds and try again before
// notifying the user of the error:
$xml = GetData($fullrequest, 10);

if (!$xml) {
    sleep(2);
    $xml = GetData($fullrequest, 10);
}

$Result = xmlparser($xml);

// Bring in the layout form
require_once('watchlayout.php');

// Here's the routine that does all the real work
function theContent() {
    global $Result;
    global $task;
    global $asins;

    // Check for errors and exit if there are any
    if (isset($Result[$task]['Items'][0]['Request'][0]['IsValid'])) {
        // The request should never be invalid, but we check anyway
        if ($Result[$task]['Items'][0]['Request'][0]['IsValid'] == 'False') {
            echo '<h2>Sorry, your request is invalid</h2>';
            return;
        } elseif (isset($Result[$task]['Items'][0]['Request']['Errors']['Error'][0]['Code']))  {
            // Check for an error code
            if  ($Result[$task]['Items'][0]['Request']['Errors']['Error'][0]['Code'] == 'AWS.ECommerceService.NoExactMatches') {
                echo '<h2>Sorry, Amazon found no exact matches for your search request</h2>';
                return;
            } else {
                echo '<h2>Sorry, Amazon found the error '. $Result[$task]['Items'][0]['Request']['Errors']['Error'][0]['Code'].' with your request';
                return;
            }
        }
    } else {
        // If nothing is returned, it is probably a 500 or 503 HTTP error
        echo '<h2>Sorry, We are having trouble connecting to Amazon. Try again in a few minutes.</h2>';
        return;
    }

    // If there is nothing in the $asins string, then we display search results
    if ($asins == '') {
        echo '<form action="" method="post" name="watch_compare" target="_self" id="watch_compare"><input type="submit" name="compare" value="Compare"><br /><br /></ <table>';

        foreach ($Result['ItemSearchResponse']['Items'][0]['Item'] as $item) {

            $image = (isset($item['SmallImage']['URL'])) ? $item['SmallImage']['URL'] : NO_IMAGE;
            $title = $item['ItemAttributes']['Title'];

            // Check for item availability
            $yourprice=(isset($item["OfferSummary"]["LowestNewPrice"]["FormattedPrice"]))? $item["OfferSummary"]["LowestNewPrice"]["FormattedPrice"] : '';

            // If we can't find a price in new condition, skip this item
            if ($yourprice == '') continue;

            // Display the Brand, which may be under 'Manufacturer' or
            // under 'Brand'
            if (isset($item['Manufacturer'])) {
                $brand = $item['Manufacturer'];
            } else if (isset($item['ItemAttributes']['Brand'])) {
                $brand = $item['ItemAttributes']['Brand'];
            } else {
                $brand = '';
            }

            // The output string
            $outstring= '<tr><td><input type="checkbox" name="'.$item['ASIN'][0].'" value="asin"/></td><td><div><img src="'.$image.'" /></div><div><a href="'.$item['DetailPageURL'].'" class="boxname" target="_blank">'. $title .'</a></div><div class="boxpublisher">'. $brand. '</div><div class="boxprice">'. $yourprice.'<br /></ div></td></tr>';

            echo $outstring;
        }

        echo '</table><input type="submit" name="compare" value="Compare"></form>';
    } else {
        // Here�s the product detail comparision output
        echo '<table>';

        // The real name of each displayed item attribute
        $row=array(
        '<tr><td style="text-align: left; height:80px"></td>',
        '<tr><td style="text-align: left">Name:</td>',
        '<tr><td style="text-align: left">Brand:</td>',
        '<tr><td style="text-align: left">Your Price:</td>',
        '<tr><td style="text-align: left">Model:</td>',
        '<tr><td style="text-align: left">Wrist Band:</td>',
        '<tr><td style="text-align: left">Clasp Type:</td>',
        '<tr><td style="text-align: left">Bezel:</td>',
        '<tr><td style="text-align: left">Dial Color:</td>',
        '<tr><td style="text-align: left">Calendar Type:</td>',
        '<tr><td style="text-align: left">Casing:</td>',
        '<tr><td style="text-align: left">Metal Type:</td>',
        '<tr><td style="text-align: left">Watch Movement:</td>',
        '<tr><td style="text-align: left">Water Resistance Depth:</td>',
        '<tr><td style="text-align: left">Weight:</td>'
        );

        // Go through each item
        foreach ($Result['ItemLookupResponse']['Items'][0]['Item'] as $item) {

            $image = (isset($item['SmallImage']['URL'])) ? $item['SmallImage']['URL'] : NO_IMAGE;
            $title = (isset($item['ItemAttributes']['Title'])) ? $item['ItemAttributes']['Title'] : 'No Name'  ;
            // Check for item availability
            $yourprice=(isset($item["OfferSummary"]["LowestNewPrice"]["FormattedPrice"]))? $item["OfferSummary"]["LowestNewPrice"]["FormattedPrice"] : '';

            // Skip the item if it�s not available
            if ($yourprice == '') continue;

            // Display the Brand, which may be under �Manufacturer� or
            // under �Brand�
            if (isset($item['Manufacturer'])) {
                $brand = $item['Manufacturer'];
            } else if (isset($item['ItemAttributes']['Brand'])) {
                $brand = $item['ItemAttributes']['Brand'];
            } else {
                $brand = '-';
            }

            // Retrieve all the item attributes we�re going to display
            // Note that they may be empty because they weren�t entered
            // into Amazon�s database properly.
            $bandm = (isset($item["ItemAttributes"]["BandMaterialType"])  and (trim($item["ItemAttributes"]["BandMaterialType"]) != ''))  ? $item["ItemAttributes"]["BandMaterialType"] : '-';
            $bezelm = (isset($item["ItemAttributes"]["BezelMaterialType"])  and (trim($item["ItemAttributes"]["BezelMaterialType"]) != ''))  ? $item["ItemAttributes"]["BezelMaterialType"] : '-';
            $casem = (isset($item["ItemAttributes"]["CaseMaterialType"])  and (trim($item["ItemAttributes"]["CaseMaterialType"]) != ''))  ? $item["ItemAttributes"]["CaseMaterialType"] : '-';
            $calt = (isset($item["ItemAttributes"]["CalendarType"])  and (trim($item["ItemAttributes"]["CalendarType"]) != ''))  ? $item["ItemAttributes"]["CalendarType"] : '-';
            $claspt = (isset($item["ItemAttributes"]["ClaspType"])  and (trim($item["ItemAttributes"]["ClaspType"]) != ''))  ? $item["ItemAttributes"]["ClaspType"] : '-';
            $dialc = (isset($item["ItemAttributes"]["DialColor"])  and (trim($item["ItemAttributes"]["DialColor"]) != ''))  ? $item["ItemAttributes"]["DialColor"] : '-';
            $metals = (isset($item["ItemAttributes"]["MetalStamp"])  and (trim($item["ItemAttributes"]["MetalStamp"]) != ''))  ? $item["ItemAttributes"]["MetalStamp"] : '-';
            $model = (isset($item["ItemAttributes"]["Model"])  and (trim($item["ItemAttributes"]["Model"]) != ''))  ? $item["ItemAttributes"]["Model"] : '-';
            $watchmt = (isset($item["ItemAttributes"]["WatchMovementType"])  and (trim($item["ItemAttributes"]["WatchMovementType"]) != ''))  ? $item["ItemAttributes"]["WatchMovementType"] : '-';
            $weight = (isset($item["ItemAttributes"]["Weight"]["Weight"])  and (trim($item["ItemAttributes"]["Weight"]["Weight"]) != '')) ? $item["ItemAttributes"]["Weight"]["Weight"] : '-';
            $weightu = (isset($item["ItemAttributes"]["Weight"]["Units"])  and (trim($item["ItemAttributes"]["Weight"]["Units"]) != '')) ? $item["ItemAttributes"]["Weight"]["Units"] : '';
            $waterr = (isset($item["ItemAttributes"]["WaterResistanceDepth"]["WaterResistanceDepth"])  and (trim($item["ItemAttributes"]["WaterResistanceDepth"]["WaterResistanceDepth"]) != ''))  ? $item["ItemAttributes"]["WaterResistanceDepth"]["WaterResistanceDepth"] : '-';
            $waterru = (isset($item["ItemAttributes"]["WaterResistanceDepth"]["Units"])  and (trim($item["ItemAttributes"]["WaterResistanceDepth"]["Units"]) != ''))  ? $item["ItemAttributes"]["WaterResistanceDepth"]["Units"] : '';

            // Output the item attributes
            $row[0] .= '<td style="text-align: center; height:80px"><img src="'. $image.'" alt="'.$title.'" /></td>';
            $row[1] .= '<td style="text-align: center"><a href="'. $item['DetailPageURL'].'" class="boxname" target="_blank" title="'.$title.'">'. $title .'</a></td>';
            $row[2] .= '<td style="text-align: center">'. $brand. '</td>';
            $row[3] .= '<td style="text-align: center">'. $yourprice.'</td>';
            $row[4] .= '<td style="text-align: center">'.$model.'</td>';
            $row[5] .= '<td style="text-align: center">'.$bandm.'</td>';
            $row[6] .= '<td style="text-align: center">'.$claspt.'</td>';
            $row[7] .= '<td style="text-align: center">'.$bezelm.'</td>';
            $row[8] .= '<td style="text-align: center">'.$dialc.'</td>';
            $row[9] .= '<td style="text-align: center">'.$calt.'</td>';
            $row[10] .= '<td style="text-align: center">'.$casem.'</td>';
            $row[11] .= '<td style="text-align: center">'.$metals.'</td>';
            $row[12] .= '<td style="text-align: center">'.$watchmt.'</td>';
            $row[13] .= '<td style="text-align: center">'.$waterr.' '.$waterru.'</td> ';
            $row[14] .= '<td style="text-align: center">'.$weight.' '.$weightu.'</td> ';
        }

        foreach ($row as $r) {
            $r .= '</tr>';
            echo $r;
        }

        echo '</table>';
    }

}

?>
