<?php 

// NEW: Only 10 pages of data allowed to be accessed  :-(

require_once("tools.inc.php");

define('SEARCH', 'Lou Donaldson');         // The artist
define('ASSOCIATES_ID','ws');              // Your Associates id
define('VERSION','2011-08-01');            // The version of AES

$ACCESS_KEY_ID = "XXXXXXXXXXXXXXXXXX";
$SECRET_ACCESS_KEY = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

// A 'no image' image to use
define ('NOIMAGE', 'http://g-images.amazon.com/images/G/01/x-site/icons/no-img-lg.gif');
// If we don't have a page parameter, this is the default page
define('DEFAULT_PAGE', '1');

// Get the page parameter from the GET request
$page=(isset($_GET['page'])) ? $_GET['page'] : DEFAULT_PAGE;

$timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z");
$timestamp = str_replace(':','%3A',$timestamp);

// Make the request for more CDs
$requestparms= 'AWSAccessKeyId='.$ACCESS_KEY_ID.'&Artist=Lou%20Donaldson&AssociateTag='.ASSOCIATES_ID.'&ItemPage='. $page.'&Operation=ItemSearch&ResponseGroup=Small,Images&SearchIndex=Music&Service=AWSECommerceService&Timestamp='.$timestamp.'&Version='.VERSION;

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

// Get the response from Amazon
$xml = file_get_contents($fullrequest);

// Parse the results
$Result = xmlparser($xml);

// The main display routine
function theContent() {
    global $Result;

    echo '<table cellspacing="2" cellpadding="2"><tr>';
    $rowcount=0;
    // Display two rows of CDs
    foreach ($Result['ItemSearchResponse']['Items'][0]['Item'] as $item) {
        // Show the CD image, else the no image image
        if (isset($item['MediumImage']['URL'])) {
            $image='<img src="'.$item["MediumImage"]["URL"].'" />';
        } else {
            $image='<img src="'.NOIMAGE.'" />';
        }
        // Get the CD title
        $title = '<a href="'.$item["DetailPageURL"].'" target="_blank">'. $item["ItemAttributes"]["Title"].'</a>';
        // After displaying five CDs, move to the next line
        if (is_int($rowcount/5)) echo '</tr><tr>';
        echo '<td><div style="text-align: center;">'.$title.'</div><div style="text-align: center;">'.$image.'</div></td>';
        $rowcount++;
    }
    echo '</tr></table';
    return;
}

// Display the pagination footer
function thePagination() {
    global $Result;
    global $page;

    $tp = $Result['ItemSearchResponse']['Items'][0]['TotalPages'];
    $tp = ($tp > 10) ? 10 : $tp; // Only 10 pages of data allowed!
    echo '<div style="text-align: left;"><b>More results.......</div><div>';
    // Display a clickable page number
    for ($i=1; $i <= $tp; $i++)
    {
        $p = ($i == $page) ? $page : '<a href="'.$_SERVER["PHP_SELF"].'?page='.$i.'">'.$i.'</a>';
        echo $p.'&nbsp;';
    }
    echo '</b></div>';
    return;
}

?>
<html>
<head>
<title>Lou Donaldson CDs</title>
</head>
<body>
<table width="100%" border="1" cellpadding="2" cellspacing="2">
   <tr>
    <td width="100%" height="250"><?php theContent(); ?> </td>
  </tr>
   <tr>
    <td colspan="2">
  <table width="100%">
    <tr>
     <td width="100%"> <?php thePagination(); ?></td>
    </tr>
  </table>
</td>
</tr>
</table>
</body>
</html>
