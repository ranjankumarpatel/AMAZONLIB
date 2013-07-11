<?php 

require_once("tools.inc.php");

define ('SORT_RELDATE', 'orig-rel-date');
define ('SORT_BESTSELLING', 'salesrank');
define ('SORT_CHEAPEST', 'price');
define ('SORTAZ', 'titlerank');
define ('SORTZA', '-titlerank');
define ('DEFAULT_SORT', 'psrank');

define('SEARCH', 'Lou Donaldson');
define('ASSOCIATES_ID','ws');
define('VERSION','2011-08-01');

$ACCESS_KEY_ID = "XXXXXXXXXXXXXXXXXX";
$SECRET_ACCESS_KEY = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

define ('NOIMAGE', 'http://g-images.amazon.com/images/G/01/x-site/icons/no-img-lg.gif');
define('DEFAULT_PAGE', '1');

$page=(isset($_GET['page'])) ? $_GET['page'] : DEFAULT_PAGE;
$sort=(isset($_GET['sort'])) ? $_GET['sort'] : DEFAULT_SORT;

$timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z");
$timestamp = str_replace(':','%3A',$timestamp);

// Make the request for more CDs
$requestparms= 'AWSAccessKeyId='.$ACCESS_KEY_ID.'&Artist=Lou%20Donaldson&AssociateTag='.ASSOCIATES_ID.'&ItemPage='. $page.'&Operation=ItemSearch&ResponseGroup=Small,Images&SearchIndex=Music&Service=AWSECommerceService&Sort='.$sort.'&Timestamp='.$timestamp.'&Version='.VERSION;

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

//echo $request;
$xml = file_get_contents($fullrequest);
//echo $xml; die();
$Result = xmlparser($xml);

function theContent() {
    global $Result;

    echo '<table cellspacing="2" cellpadding="2"><tr>';
    $rowcount=0;
    foreach ($Result['ItemSearchResponse']['Items'][0]['Item'] as $item) {
        if (isset($item['MediumImage']['URL'])) {
            $image='<img src="'.$item["MediumImage"]["URL"].'" />';
        } else {
            $image='<img src="'.NOIMAGE.'" />';
        }
        $title = '<a href="'.$item["DetailPageURL"].'" target="_blank">'.$item["ItemAttributes"]["Title"].'</a>';
        if (is_int($rowcount/5)) echo '</tr><tr>';

        echo '<td><div style="text-align: center;">'.$title.'</div><div style="text-align: center;">'.$image.'</div></td>';
        $rowcount++;
    }
    echo '</tr></table';
    return;
}


function theLeftMenu() {
    echo '<div><h3><a href="'.$_SERVER["PHP_SELF"].'?sort=salesrank">Best Sellers</a></h3></div>
    <div><h3><a href="'.$_SERVER["PHP_SELF"].'?sort=orig-rel-date">Latest Releases</a></h3></div>
    <div><h3><a href="'.$_SERVER["PHP_SELF"].'?sort=price">Least Expensive</a></h3></div>
    <div><h3><a href="'.$_SERVER["PHP_SELF"].'?sort=titlerank">All CDs, A-Z</a></h3></div>
    <div><h3><a href="'.$_SERVER["PHP_SELF"].'?sort=-titlerank">All CDs, Z-A</a></h3></div>';
}


function thePagination() {
    global $Result;
    global $page;
    global $sort;

    echo '<div style="text-align: left;"><b>More results.......</div><div>';
    $totalpages = $Result['ItemSearchResponse']['Items'][0]['TotalPages'];
    if ($totalpages > 10) $totalpages = 10;  // only 10 pages allowed!
    for ($i=1; $i <= $totalpages; $i++)
    {
        $p = ($i == $page) ? $page : '<a href="'.$_SERVER["PHP_SELF"].'?page='.$i.'&sort='.$sort.'">'.$i.'</a>';
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
<table width="1000" border="1" cellpadding="2" cellspacing="2">
  <tr>
      <td width="25%" height="250">
      <?php theLeftMenu(); ?>
          </td>
    <td width="75%" height="250"><?php theContent(); ?> </td>
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
