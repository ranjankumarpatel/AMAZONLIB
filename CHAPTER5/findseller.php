<?php 

require_once("tools.inc.php");

define('VERSION', '2011-08-01');
define('ASSOCIATES_ID','ws');
define('DEFAULT_SORT', 'salesrank');
define('NO_ASIN', '0385504209');
define('NO_AVGFEEDBACK', '');
define('NO_FEEDBACK', '');
define('NO_CONDITION', 'All');
define('NO_AVAILABILITY', '');
define('NO_KEYWORDS', '');
define('DEFAULT_STATE', 'All');

$ACCESS_KEY_ID = "XXXXXXXXXXXXXXXXXX";
$SECRET_ACCESS_KEY = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

// Paging parameters
$customerid=(empty($_GET['customerid'])) ? '' : $_GET['customerid'] ;
$sellerid=(empty($_GET['sellerid'])) ? '' : $_GET['sellerid'] ;
$offerpage=(empty($_GET['offerpage'])) ? 1 : $_GET['offerpage']  ;
$variationpage=(empty($_GET['variationpage'])) ? 1 : $_GET['variationpage'] ;
$reviewpage=(empty($_GET['reviewpage'])) ? 1 : $_GET['reviewpage'] ;
$rpage=(empty($_GET['rpage'])) ? 1 : $_GET['rpage'] ;

// Form values
$asin=(empty($_GET['asin'])) ? NO_ASIN : trim($_GET['asin']) ;
$condition=(empty($_GET['condition'])) ? NO_CONDITION : $_GET['condition'] ;
$states=array();

if (empty($_GET['states'])) {
    $states[]= DEFAULT_STATE;
} else {
    $states = $_GET['states'];
}

// For the next fetch.....
$str='';
foreach ($states as $state) {
    $str .= '&states%5B%5D='.$state;
}

$getagain = '&asin='.$asin.'&condition='.$condition.$str;

function select_condition ($t) {
    global $condition;
    if ($condition == $t) { echo 'selected'; }
}

function select_state ($t) {
    global $states;
    if (in_array($t, $states)) { echo 'selected'; }
}

$timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z");
$timestamp = str_replace(':','%3A',$timestamp);

//DISPLAY SEARCH FORM
require_once('search_display.php');

if (!empty($sellerid)) {
    $nextpage=$reviewpage+1;
    $requestparms='AWSAccessKeyId='.$ACCESS_KEY_ID.'&AssociateTag='.ASSOCIATES_ID.'&Operation=SellerLookup&SellerLookup.1.FeedbackPage='.$reviewpage.'&SellerLookup.2.FeedbackPage='.$nextpage.'&SellerLookup.Shared.SellerId='.$sellerid.'&Service=AWSECommerceService&Timestamp='.$timestamp.'&Version='.VERSION;

} elseif (!empty($customerid)) {
    $nextpage=$rpage+1;
    $requestparms='AWSAccessKeyId='.$ACCESS_KEY_ID.'&AssociateTag='.ASSOCIATES_ID.'&CustomerContentLookup.1.ReviewPage='.$rpage.'&CustomerContentLookup.2.ReviewPage='.$nextpage.'&CustomerContentLookup.Shared.CustomerId='.$customerid.'&Operation=CustomerContentLookup&ResponseGroup=CustomerFull,Request&Service=AWSECommerceService&Timestamp='.$timestamp.'&Version='.VERSION;

} else {
    $nextvariationpage=$variationpage+1;
    $nextofferpage=$offerpage+1;
    $requestparms='AWSAccessKeyId='.$ACCESS_KEY_ID.'&AssociateTag='.ASSOCIATES_ID.'&ItemLookup.1.OfferPage='.$offerpage.'&ItemLookup.1.VariationPage='.$variationpage.'&ItemLookup.2.OfferPage='.$nextofferpage.'&ItemLookup.2.VariationPage='.$nextvariationpage.'&ItemLookup.Shared.Condition='.$condition.'&ItemLookup.Shared.ItemId='.$asin.'&ItemLookup.Shared.MerchantId=All&ItemLookup.Shared.ResponseGroup=OfferFull,Variations,Small&Operation=ItemLookup&Service=AWSECommerceService&Timestamp='.$timestamp.'&Version='.VERSION;

}

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

if (!empty($sellerid)) {
    process_seller($reviewpage);
} elseif (!empty($customerid)) {
    process_customer($rpage);
} elseif (isset($Result['ItemLookupResponse']['Items'][0]['Item'][0]['VariationSummary'])) {
    process_parent_asin($variationpage);
} else {
    process_regular_asin($offerpage);
}

// Only returns publicly accessible info --- usually next to nothing
function process_customer($rpage) {
    global $Result;

    $nickname=(isset($Result['CustomerContentLookupResponse']['Customers'][0]['Customer'][0]['Nickname'])) ? $Result['CustomerContentLookupResponse']['Customers'][0]['Customer'][0]['Nickname'] : 'n/a';
    $wishlist=(isset($Result['CustomerContentLookupResponse']['Customers'][0]['Customer'][0]['WishListId'])) ? $Result['CustomerContentLookupResponse']['Customers'][0]['Customer'][0]['WishListId'] : 'n/a';
    $birthday=(isset($Result['CustomerContentLookupResponse']['Customers'][0]['Customer'][0]['Birthday'])) ? $Result['CustomerContentLookupResponse']['Customers'][0]['Customer'][0]['Birthday'] : 'n/a';
    $city=(isset($Result['CustomerContentLookupResponse']['Customers'][0]['Customer'][0]['Location']['City'])) ? $Result['CustomerContentLookupResponse']['Customers'][0]['Customer'][0]['Location']['City'] : 'n/a';
    $state=(isset($Result['CustomerContentLookupResponse']['Customers'][0]['Customer'][0]['Location']['State'])) ? $Result['CustomerContentLookupResponse']['Customers'][0]['Customer'][0]['Location']['State'] : 'n/a';
    $country=(isset($Result['CustomerContentLookupResponse']['Customers'][0]['Customer'][0]['Location']['Country'])) ? $Result['CustomerContentLookupResponse']['Customers'][0]['Customer'][0]['Location']['Country'] : 'n/a';


    echo "<b>Customer Nickname: </b>".$nickname." <br />";
    echo "<b>Customer Wishlist: </b>".$wishlist." <br />";
    echo "<b>Customer Birthday: </b>".$birthday." <br />";
    echo "<b>Customer City: </b>".$city." <br />";
    echo "<b>Customer State: </b>".$state." <br />";
    echo "<b>Customer Country: </b>".$country." <br />";

    $display='';

    echo '<br /><br />Seller feedback pages (Up to 5 reviews per page, two pages at a time).......';

    if (isset($Result['CustomerContentLookupResponse']['Customers'][0]['Customer'][0]['Reviews']['TotalReviewPages']) and ($Result['CustomerContentLookupResponse']['Customers'][0]['Customer'][0]['Reviews']['TotalReviewPages'] != '0')) {

        for ($i=1; $i <= $Result['CustomerContentLookupResponse']['Customers'][0]['Customer'][0]['Reviews']['TotalReviewPages']; $i=$i+2)
        {
            $nextpage=$i+1;
            $page = ($i == $rpage) ? $rpage.'-'.$nextpage : '<a href="findseller.php?&rpage='.$i.'">'.$i.'-'.$nextpage.'</a>';
            $display  .= $page.'&nbsp;';
            if ($i == 9) break;
        }
    }

    echo $display.'<br />';

    foreach( $Result['CustomerContentLookupResponse']['Customers'] as $customer) {
        if (isset($customer['Customer'][0]['Reviews'])) {
            foreach ($customer['Customer'][0]['Reviews']['Review'] as $review)  {

                $asin = (isset($review['ASIN'][0])) ? '<b>ASIN:</b> '.$review['ASIN'][0] : '<b>ASIN:</b> n/a';

                $rating = (isset($review['Rating']))? '  <b>Rating:</b> '.$review['Rating'] : '<b>Rating:</b> n/a';

                $helpfulvotes = (isset($review['HelpfulVotes'])) ? '  <b>Helpful Votes:</b> '.$review['HelpfulVotes'] : ' <b>Helpful Votes:</b> n/a';

                $totalvotes = (isset($review['TotalVotes'])) ? '  <b>Total Votes:</b> '.$review['TotalVotes'] : ' <b>Total Votes:</b> n/a';

                // Still in Unix time format?
                $date = (isset($review['Date'])) ? '  <b>Rating Date:</b> '.$review['Date'] : ' <b>Rating Date:</b> n/a';
                $summary = (isset($review['Summary'])) ? '  <b>Summary:</b> '.$review['Summary'] : ' <b>Summary:</b> n/a';
                $content = (isset($review['Content'])) ? '  <b>Content:</b> '.$review['Content'] : ' <b>Content:</b> n/a';

                echo '<br /><br />'.$asin.$rating.$helpfulvotes.$totalvotes.$date.$summary.'<br />'.$content;
            }
        }
    }
    return;
}


// Two different sets of results if we use MerchantId instead of SellerId
function process_seller($reviewpage) {
    global $Result;
    global $sellerid;

    $sellername= (isset($Result['SellerLookupResponse']['Sellers'][0]['Seller'][0]['SellerName'])) ? $Result['SellerLookupResponse']['Sellers'][0]['Seller'][0]['SellerName'] : 'n/a';
    $nickname=(isset($Result['SellerLookupResponse']['Sellers'][0]['Seller'][0]['Nickname'])) ? $Result['SellerLookupResponse']['Sellers'][0]['Seller'][0]['Nickname'] : 'n/a';
    $city=(isset($Result['SellerLookupResponse']['Sellers'][0]['Seller'][0]['Location']['City'])) ? $Result['SellerLookupResponse']['Sellers'][0]['Seller'][0]['Location']['City'] : 'n/a';
    $state=(isset($Result['SellerLookupResponse']['Sellers'][0]['Seller'][0]['Location']['State'])) ? $Result['SellerLookupResponse']['Sellers'][0]['Seller'][0]['Location']['State'] : 'n/a';
    $about=(isset($Result['SellerLookupResponse']['Sellers'][0]['Seller'][0]['About'])) ? $Result['SellerLookupResponse']['Sellers'][0]['Seller'][0]['About'] : 'n/a';
    $totalpages=(isset($Result['SellerLookupResponse']['Sellers'][0]['Seller'][0]['TotalFeedbackPages'])) ? $Result['SellerLookupResponse']['Sellers'][0]['Seller'][0]['TotalFeedbackPages'] : 'n/a';
    $averagefeed=(isset($Result['SellerLookupResponse']['Sellers'][0]['Seller'][0]['AverageFeedbackRating'])) ? $Result['SellerLookupResponse']['Sellers'][0]['Seller'][0]['AverageFeedbackRating'] : 'n/a';



    echo "<br /><b>Seller Name: </b>".$sellername."<br />";
    echo "<b>Seller Nickname: </b>".$nickname." <br />";
    echo "<b>Seller Location: </b>".$city.",".$state." <br />";
    echo "<b>Seller Feedback Rating (average): </b>".$averagefeed." <br />";
    echo "<b>Total Feedback: </b>".$totalpages."<br />";
    echo "<b>About this seller: </b>".$about;

    $display='';
    echo '<br /><br />Seller feedback pages (Up to 5 reviews per page, two pages at a time).......';
    if (isset($Result['SellerLookupResponse']['Sellers'][0]['Seller'][0]['TotalFeedbackPages'])) {
        for ($i=1; $i <= $Result['SellerLookupResponse']['Sellers'][0]['Seller'][0]['TotalFeedbackPages']; $i=$i+2)
        {
            $nextpage=$i+1;
            $page = ($i == $reviewpage) ? $reviewpage.'-'.$nextpage : '<a href="findseller.php?sellerid='.$sellerid.'&reviewpage='.$i.'">'.$i.'-'.$nextpage.'</a>';
            $display  .= $page.'&nbsp;';
            if ($i == 9) break;
        }
    }
    echo $display.'<br />';

    foreach ($Result['SellerLookupResponse']['Sellers'] as $seller)  {
        if (!isset($seller['Seller'][0]['SellerFeedback']['Feedback'])) continue;
        foreach ($seller['Seller'][0]['SellerFeedback']['Feedback'] as $review) {

            $rating = (isset($review['Rating'])) ? '<b>Rating:</b> '.$review['Rating'] : '<b>Rating:</b> n/a';

            $comment = (isset($review['Comment']))? '  <b>Comment:</b> '.$review['Comment'] : '<b>Comment:</b> n/a';

            $date = (isset($review['Date'])) ? '  <b>Rating Date:</b> '.date('l, F j, Y, g:ia T', strtotime(str_replace('T', ' ', $review['Date']))) : ' <b>Rating Date:</b> n/a';

            $ratedby = (isset($review['RatedBy'])) ? '  <b>Customer:</b> <a href="findseller.php?customerid='.$review['RatedBy'].' ">'.$review['RatedBy'].'</a>' : '  <b>Customer:</b>  n/a';

            echo '<br /><br />'.$rating.$ratedby.$date.'<br />'.$comment;
        }
    }
    return;
}


function process_regular_asin($offerpage) {
    global $Result;
    global $states;
    global $condition;
    global $getagain;

    echo '<br />ITEM: '.$Result['ItemLookupResponse']['Items'][0]['Item'][0]['ItemAttributes']['Title'].'<br />';
    echo 'PRODUCT GROUP: '.$Result['ItemLookupResponse']['Items'][0]['Item'][0]['ItemAttributes']['ProductGroup'].'<br />';
    $totaloffers= (isset($Result['ItemLookupResponse']['Items'][0]['Item'][0]['Offers']['TotalOffers'])) ? $Result['ItemLookupResponse']['Items'][0]['Item'][0]['Offers']['TotalOffers'] : 'none';
    echo '<br />TOTAL OFFERS IN <b>'.$condition.'</b> CONDITION(s): <b>'. $totaloffers .'</b><br />';
    echo 'New: '.$Result['ItemLookupResponse']['Items'][0]['Item'][0]['OfferSummary']['TotalNew'].'<br />';
    echo 'Used: '.$Result['ItemLookupResponse']['Items'][0]['Item'][0]['OfferSummary']['TotalUsed'].'<br />';
    echo 'Refurbished: '.$Result['ItemLookupResponse']['Items'][0]['Item'][0]['OfferSummary']['TotalRefurbished'].'<br />';
    echo 'Collectible: '.$Result['ItemLookupResponse']['Items'][0]['Item'][0]['OfferSummary']['TotalCollectible'].'<br />';

    $display='';
    echo '<br /><br />Offer pages (Up to 10 offers per page - up to 100 pages shown here).......';
    if (isset($Result['ItemLookupResponse']['Items'][0]['Item'][0]['Offers']['TotalOfferPages'])) {
    	$showoffers = ($Result['ItemLookupResponse']['Items'][0]['Item'][0]['Offers']['TotalOfferPages'] < 100) ? 
    		$Result['ItemLookupResponse']['Items'][0]['Item'][0]['Offers']['TotalOfferPages'] : 100;
        for ($i=1; $i <= $showoffers; $i=$i+2)
        {
            $nextpage=$i+1;
            $page = ($i == $offerpage) ? $offerpage.'-'.$nextpage : '<a href="findseller.php?offerpage='.$i.$getagain.'">'.$i.'-'.$nextpage.'</a>';
            $display  .= $page.'&nbsp;';
        }
    }
    echo $display.'<br /><br />';
    echo 'Filters: <br />';
    echo 'Only sellers in these states are shown: '.implode(', ',$states).'<br />';
    echo 'Only items in this condition are shown: '.$condition.'<br />';

    foreach ($Result['ItemLookupResponse']['Items'] as $items) {
        if (!isset($items['Item'][0]['Offers']['Offer'])) continue;
        foreach ($items['Item'][0]['Offers']['Offer'] as $offer) {

            // echo '<pre>';
            // print_r($offer);
            //echo '</pre>';

            $state = (isset($offer['Merchant']['Location']['StateCode'])) ? strtoupper($offer['Merchant']['Location']['StateCode']) : 'NO_STATE';

            // Only show sellers in selected states
            if ($states[0] != DEFAULT_STATE) {
                if (!in_array($state, $states)) continue;
            }

            $price = (isset($offer['OfferListing'][0]['Price']['FormattedPrice'])) ? '<b>Price:</b> '.$offer['OfferListing'][0]['Price']['FormattedPrice'] : '<b>Price:</b> n/a';

            $condition = (isset($offer['OfferAttributes']['Condition']))? '  <b>Condition:</b> '.$offer['OfferAttributes']['Condition'] : '<b>Condition:</b> n/a';

            $name = (isset($offer['Seller'][0]['SellerName'])) ? '  <b>Seller Name:</b> '.$offer['Seller'][0]['SellerName'] : ' <b>Seller name:</b> n/a';

            $sellerid = (isset($offer['Seller'][0]['SellerId'])) ? $offer['Seller'][0]['SellerId'] : '';
            $nickname = (isset($offer['Seller'][0]['Nickname'])) ? $offer['Seller'][0]['Nickname'] : '';

            if ($nickname != '' and $sellerid != '') {
                $nickname = '  <b>Nickname:</b> <a href="findseller.php?sellerid='.$sellerid.' ">'.$nickname.'</a>';
            } elseif ($nickname != '') {
                $nickname='  <b>Nickname:</b> '.$nickname;
            } else {
                $nickname =  '  <b>Nickname:</b> n/a';
            }

            $merchantname = (isset($offer['Merchant']['Name'])) ? $offer['Merchant']['Name'] : '';
            $merchantid = (isset($offer['Merchant']['MerchantId'])) ? $offer['Merchant']['MerchantId'] : '';

            if ($merchantname != '' and $merchantid != '') {
                $merchantname = '  <b>Merchant Name:</b> <a href="findseller.php?sellerid='.$merchantid.'">'.$merchantname.'</a>';
            } elseif ($merchantname != '') {
                $merchantname='  <b>Merchant Name:</b> '.$merchantname;
            } else {
                $merchantname =  '  <b>Merchant Name:</b> n/a';
            }

            if ((isset($offer['Seller'][0]['AverageFeedbackRating'])) and $offer['Seller'][0]['AverageFeedbackRating'] == '0.0') {
                $avgfeedb='<b>No feedback for this seller</b>';
                $totalfeedb='';
            } else {
                $avgfeedb = (isset($offer['Seller'][0]['AverageFeedbackRating'])) ? '  <b>Average Rating:</b> '.$offer['Seller'][0]['AverageFeedbackRating'].' out of 5.0' : '<b>Average rating:</b> n/a';
                $totalfeedb = (isset($offer['Seller'][0]['TotalFeedback'])) ? '  <b>Total Feedback:</b> '.$offer['Seller'][0]['TotalFeedback'] : '<b>Total Feedback:</b> n/a';
            }

            $conditionnote = (isset($offer['OfferAttributes']['ConditionNote'])) ? '<br /><b>Condition notes:</b> '.$offer['OfferAttributes']['ConditionNote'] : '<b>Condition notes:</b> n/a';

            $avail =  (isset($offer['OfferListing'][0]['Availability'])) ? '<br /><b>Availability:</b> '.$offer['OfferListing'][0]['Availability'] : '<b>Availability:</b> n/a';

            echo '<br />';
            echo $price.$condition.$name.$merchantname.$nickname.$avgfeedb.$totalfeedb;
            echo $conditionnote;
            echo $avail;
            echo '<br />';
            //echo 'Seller Location: '.$offer['Seller'][0]['Location']['State'].'<br />'
        }
    }
    return;
}

//B0002FHIVM
//B00067VWEU
//B000689AD4
//B0001YR1RU
//B00006TY86 -- Not accesible via AWS
//B0001YR26U -- invalid asin
function process_parent_asin($variationpage) {
    global $Result;
    global $getagain;

    /*
    echo '<pre>';
    print_r($Result);
    echo '</pre>';
    */

    echo '<br />ITEM: '.$Result['ItemLookupResponse']['Items'][0]['Item'][0]['ItemAttributes']['Title'].'<br />';
    echo 'PRODUCT GROUP: '.$Result['ItemLookupResponse']['Items'][0]['Item'][0]['ItemAttributes']['ProductGroup'].'<br /><br />';

    $lowestn=(isset($Result['ItemLookupResponse']['Items'][0]['Item'][0]['VariationSummary']['LowestPrice']['FormattedPrice'])) ? $Result['ItemLookupResponse']['Items'][0]['Item'][0]['VariationSummary']['LowestPrice']['FormattedPrice']  : 'n/a';
    $highestn= (isset($Result['ItemLookupResponse']['Items'][0]['Item'][0]['VariationSummary']['HighestPrice']['FormattedPrice'])) ? $Result['ItemLookupResponse']['Items'][0]['Item'][0]['VariationSummary']['HighestPrice']['FormattedPrice']  : 'n/a';
    $lowestsale= (isset($Result['ItemLookupResponse']['Items'][0]['Item'][0]['VariationSummary']['LowestSalePrice']['FormattedPrice'])) ? $Result['ItemLookupResponse']['Items'][0]['Item'][0]['VariationSummary']['LowestSalePrice']['FormattedPrice']  : 'n/a';
    $highestsale= (isset($Result['ItemLookupResponse']['Items'][0]['Item'][0]['VariationSummary']['HighestSalePrice']['FormattedPrice'])) ? $Result['ItemLookupResponse']['Items'][0]['Item'][0]['VariationSummary']['HighestSalePrice']['FormattedPrice']  : 'n/a';
    $totalvariations= (isset($Result['ItemLookupResponse']['Items'][0]['Item'][0]['Variations']['TotalVariations'])) ? $Result['ItemLookupResponse']['Items'][0]['Item'][0]['Variations']['TotalVariations']  : 'n/a';

    echo 'Lowest New Price: '.$lowestn.'<br />';
    echo 'Highest New Price: '.$highestn.'<br />';
    echo 'Lowest Sale Price: '.$lowestsale.'<br />';
    echo 'Highest Sale Price: '.$highestsale.'<br />';

    echo '<br />Total Variations: '.$totalvariations.'<br />';

    $display='';
    echo '<br /><br />Variations pages (Up to 10 variations per page).......';
    for ($i=1; $i <= $Result['ItemLookupResponse']['Items'][0]['Item'][0]['Variations']['TotalVariationPages']; $i=$i+2)
    {
        $nextpage=$i+1;
        $page = ($i == $variationpage) ? $variationpage.'-'.$nextpage : '<a href="findseller.php?variationpage='.$i.$getagain.'">'.$i.'-'.$nextpage.'</a>';
        $display  .= $page.'&nbsp;';
    }
    echo $display.'<br /><br />';

    foreach ($Result['ItemLookupResponse']['Items'] as $items) {
        if (!isset($items['Item'][0]['Variations']['Item'])) continue;
        foreach ($items['Item'][0]['Variations']['Item'] as $offers) {

            $childasin=$offers['ASIN'][0];
            $title=$offers['ItemAttributes']['Title'];
            //$offers['ItemAttributes']['Title']='';
            //$details=implode('; ',$offers['ItemAttributes']);
            echo 'Product: <a href="findseller.php?asin='.$childasin.'">'.$title.'</a><br />';
            //echo "Details: ".$details."<br />";
        }
    }
    return;
}
?>

</body>
</html>
