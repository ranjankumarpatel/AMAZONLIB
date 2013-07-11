<?php 

error_reporting(E_ALL);

die("This sample app will no longer work because the MultiOperation operation has been deprecated by Amazon.");

require_once("tools.inc.php");
define('ASSOCIATES_ID','ws');
define('VERSION','2011-08-01');

$ACCESS_KEY_ID = "XXXXXXXXXXXXXXXXXX";
$SECRET_ACCESS_KEY = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

// No Image Stub
define ('NOIMAGE_MED', 'http://g-images.amazon.com/images/G/01/x-site/icons/no-img-lg.gif');
define('FIRST_TIME', 'FIRST');

$bn=(isset($_GET['bn'])) ? $_GET['bn'] : FIRST_TIME;
$sim=(isset($_GET['sim'])) ? $_GET['sim'] : '';

$bnarray['1040660']='Women';
$bnarray['1040658']='Men';
$bnarray['1040662']='Kids and Baby';
$bnarray['1040668']='Shoes';
$bnarray['1036700']='Accessories';

$timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z");
$timestamp = str_replace(':','%3A',$timestamp);

if ($sim != '') {

    $requestparms='AWSAccessKeyId='.$ACCESS_KEY_ID.'&AssociateTag='.ASSOCIATES_ID.'&ItemLookup.1.ItemId='.$sim.'&ItemLookup.1.ResponseGroup=Small,Images&Operation=ItemLookup%2CSimilarityLookup&Service=AWSECommerceService&SimilarityLookup.1.ItemId='.$sim.'&SimilarityLookup.1.ResponseGroup=Small%2CImages&Timestamp='.$timestamp.'&Version='.VERSION;

} elseif ($bn != FIRST_TIME) {

    $requestparms='AWSAccessKeyId='.$ACCESS_KEY_ID.'&AssociateTag='.ASSOCIATES_ID.'&BrowseNodeLookup.1.BrowseNodeId='.$bn.'&ItemSearch.1.BrowseNode='.$bn.'&ItemSearch.1.MerchantId=All&ItemSearch.1.ResponseGroup=Images,Small&ItemSearch.1.SearchIndex=Apparel&Operation=ItemSearch,BrowseNodeLookup&Service=AWSECommerceService&Timestamp='.$timestamp.'&Version='.VERSION;
}

if ((!empty($sim)) or ($bn != FIRST_TIME)) {

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


function theItemWindow() {
    global $Result;
    global $bn;
    global $sim;

    $lookup = (empty($sim)) ? 'ItemSearchResponse' : 'SimilarityLookupResponse';

    echo '<table cellspacing="2" cellpadding="2"><tr>';

    if (isset($Result['MultiOperationResponse']['SimilarityLookupResponse']['Items'][0]['Request']['Errors']['Error'][0]['Code']) and ($Result['MultiOperationResponse']['SimilarityLookupResponse']['Items'][0]['Request']['Errors']['Error'][0]['Code'] == 'AWS.ECommerceService.NoSimilarities') ) {
        echo '<td><div style="text-align: center;">There are no similar products to display</div></td>';
    } else {
        $rowcount=0;
        if ($bn != FIRST_TIME or (!empty($sim))) {
            foreach ($Result['MultiOperationResponse'][$lookup]['Items'][0]['Item'] as $item) {
                if (isset($item['SmallImage']['URL'])) {
                    $image='<img src="'.$item["SmallImage"]["URL"].'" />';
                } else {
                    $image='<img src="'.NOIMAGE_MED.'" />';
                }
                $title = '<a href="'.$item["DetailPageURL"].'" target="_blank">'.$item["ItemAttributes"]["Title"].'</a>';
                $simlink = '<a href="'.$_SERVER["PHP_SELF"].'?sim='.$item["ASIN"][0].'" target="_blank">Find similar items...</a>';
                if (is_int($rowcount/5)) echo '</tr><tr>';

                echo '<td><div style="text-align: center;">'.$title.'</div><div style="text-align: center;">'.$image.'</div><div style="text-align: center;">'.$simlink.'</td>';
                $rowcount++;
            }
        } else {
            echo '<td><div style="text-align: center;">Choose a category on the left</div></td>';
        }
    }

    echo '</tr></table>';

}

function theLeftSide() {
    global $bnarray;
    global $bn;
    global $Result;

    if ($bn == FIRST_TIME) {
        foreach ($bnarray as $bnkey => $bnval) {
            echo '<div><a href="'.$_SERVER["PHP_SELF"].'?bn='.$bnkey.'">'.$bnval.'</a></div>';
        }
    }  else {
        if (isset($Result['MultiOperationResponse']['BrowseNodeLookupResponse']['BrowseNodes'][0]['BrowseNode'][0]['Children']['BrowseNode'])) {
            foreach ($Result['MultiOperationResponse']['BrowseNodeLookupResponse']['BrowseNodes'][0]['BrowseNode'][0]['Children']['BrowseNode'] as $browsenode) {
                echo '<div><a href="'.$_SERVER["PHP_SELF"].'?bn='.$browsenode['BrowseNodeId'][0].'">'.$browsenode['Name'].'</a></div>';
            }
        } else {
            echo '<div>No more child browse nodes</div>';
        }
    }
}

function theLeftSim() {
    global $Result;

    if (isset($Result['MultiOperationResponse']['ItemLookupResponse']['Items'][0]['Item'][0]['SmallImage']['URL']))
    {
        $image='<img src="'.$Result["MultiOperationResponse"]["ItemLookupResponse"]["Items"][0]["Item"][0]["SmallImage"]["URL"].'" />';
    } else {
        $image='<img src="'.NOIMAGE_MED.'" />';
    }

    $title = $Result["MultiOperationResponse"]["ItemLookupResponse"]["Items"][0]["Item"][0]["ItemAttributes"]["Title"];

    echo '<div style="text-align: center;">'.$title.'</div><div style="text-align: center;">'.$image.'</div>';

}


?>

<html>
<head>
<title>Apparel Browser</title>
</head>
<body>
<table width="1000" border="1" cellpadding="2" cellspacing="2">
    <tr>
    <td width="15%" height="250"><?php if (empty($sim)) { theLeftSide(); } else {theLeftSim(); } ?>
    </td>
    <td width="85%" height="250"><?php theItemWindow(); ?>
    </td>
  </tr>
  </table>
</body>
</html>
