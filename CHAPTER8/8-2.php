<?php 
error_reporting(E_ALL);

die("This sample app will no longer work because the MultiOperation operation has been deprecated by Amazon.");

require_once("tools.inc.php");

define('ASSOCIATES_ID','ws');
define('VERSION','2011-08-01');
define ('NOIMAGE_MED', 'http://g-images.amazon.com/images/G/01/x-site/icons/no-img-lg.gif');
define('FIRST_TIME', 'FIRST');

$ACCESS_KEY_ID = "XXXXXXXXXXXXXXXXXX";
$SECRET_ACCESS_KEY = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

$bn=(isset($_GET['bn'])) ? $_GET['bn'] : FIRST_TIME;

$bnarray['1040660']='Women';
$bnarray['1040658']='Men';
$bnarray['1040662']='Kids and Baby';
$bnarray['1036700']='Accessories';

if ($bn != FIRST_TIME) {

$timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z");
$timestamp = str_replace(':','%3A',$timestamp);

$requestparms = 'AWSAccessKeyId='.$ACCESS_KEY_ID.'&AssociateTag='.ASSOCIATES_ID.'&BrowseNodeLookup.1.BrowseNodeId='.$bn.'&ItemSearch.1.BrowseNode='.$bn.'&ItemSearch.1.MerchantId=All&ItemSearch.1.ResponseGroup=Images,Small&ItemSearch.1.SearchIndex=Apparel&Operation=ItemSearch,BrowseNodeLookup&Service=AWSECommerceService&Timestamp='.$timestamp.'&Version='.VERSION;
  
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
    echo $fullrequest; die();
    $Result = xmlparser($xml);

}

function theItemWindow() {
    global $Result;
    global $bn;

    echo '<table cellspacing="2" cellpadding="2"><tr>';
    $rowcount=0;
    if ($bn != FIRST_TIME) {
        foreach ($Result['MultiOperationResponse']['ItemSearchResponse']['Items'][0]['Item'] as $item) {
            if (isset($item['SmallImage']['URL'])) {
                $image='<img src="'.$item["SmallImage"]["URL"].'" />';
            } else {
                $image='<img src="'.NOIMAGE_MED.'" />';
            }
            $title = '<a href="'.$item["DetailPageURL"].'" target="_blank">'. $item["ItemAttributes"]["Title"].'</a>';
            if (is_int($rowcount/5)) echo '</tr><tr>';

            echo '<td><div style="text-align: center;">'.$title.'</div><div style="text-align: center;">'.$image.'</div></td>';
            $rowcount++;
        }
    } else {
        echo '<td><div style="text-align: center;">Choose a category on the left</ div></td>';
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
        if (isset($Result['MultiOperationResponse']['BrowseNodeLookupResponse']['BrowseNodes'][0 ]['BrowseNode'][0]['Children']['BrowseNode'])) {
            foreach ($Result['MultiOperationResponse']['BrowseNodeLookupResponse']['BrowseNodes'][0]['BrowseNode'][0]['Children']['BrowseNode'] as $browsenode) {
                echo '<div><a href="'.$_SERVER["PHP_SELF"].'?bn='.$browsenode['BrowseNodeId'][0].'"> '.$browsenode['Name'].'</a></div>';
            }
        } else {
            echo '<div>No more child browse nodes</div>';
        }
    }
}

?>

<html>
<head>
<title>Apparel Browser</title>
</head>
<body>
<table width="1000" border="1" cellpadding="2" cellspacing="2">
    <tr>
    <td width="15%" height="250"><?php theLeftSide(); ?>
    </td>
    <td width="85%" height="250"><?php theItemWindow(); ?>
    </td>
  </tr>
  </table>
</body>
</html>
