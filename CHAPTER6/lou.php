<?php

/* NOTE: The cart will not add more than the number of items that are available for sale,
so, if you try to increase the number of copies of a particular item to 3, but there are
only 2 copies available for sale on Amazon, it will remain at 2 after the update.  */

error_reporting(E_ALL);

define('SEARCH', 'Lou Donaldson');
define('ASSOCIATES_ID', 'webservices-20');
define('VERSION', '2011-08-01');
define('NOIMAGE', 'http://g-images.amazon.com/images/G/01/x-site/icons/no-img-lg.gif');
define('COOKIE_EXPIRATION', 90); // Number of days cookie lasts
define('COOKIE_NAME', 'lou'); // Name of cookie
define('ACCESS_KEY_ID', "XXXXXXXXXXXXXXXXXX");
define('SECRET_ACCESS_KEY', "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX");


require_once "tools.inc.php";
require_once "class.amazoncart.php";

$cookie = (empty($_COOKIE[COOKIE_NAME])) ? null : $_COOKIE[COOKIE_NAME];
$showcart = false;
$cart = new AmazonCookieCart($cookie);
$status_message='';

if (empty($_COOKIE[COOKIE_NAME])) {

	if (!empty($_GET['add'])) {

		$Result=$cart->ProcessCartRequest('create', $_GET['add']);

	}

	if (isset($_GET['showcart'])) {

		$status_message='Your shopping cart is currently empty or has expired';

	}

	ShowRecords();

} elseif (isset($_GET['add'])) {

	$Result=$cart->ProcessCartRequest('add', $_GET['add']);
	ShowRecords();

} elseif (isset($_GET['showcart'])) {

	$Result=$cart->ProcessCartRequest('show', $_GET['showcart']);
	if ($Result) {
		$showcart= true;
	} else {
		ShowRecords();
	}

} elseif (isset($_POST['updatecart'])) {

	$Result=$cart->ProcessCartRequest('modify', $_POST);
	if ($Result) {
		$showcart=true;
	} else {
		ShowRecords();
	}

} elseif (isset($_POST['checkout']) and (!empty($_POST['purchaseurl']))) {
	$cart->killcookie();
	header('Location: '.$_POST['purchaseurl']);
} else {
	ShowRecords();
}


$itemsincart = $cart->GetCartSize();
if (empty($status_message)) $status_message = $cart->GetStatusMessage();

/**
 *
 */


function ShowRecords() {
	global $Result;

	$timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z");
	$timestamp = str_replace(':', '%3A', $timestamp);

	$requestparms ='AWSAccessKeyId='.ACCESS_KEY_ID.'&Artist=Lou%20Donaldson&AssociateTag='.ASSOCIATES_ID.'&MerchantId=All&Operation=ItemSearch&ResponseGroup=Small,Images,OfferFull&SearchIndex=Music&Service=AWSECommerceService&Timestamp='.$timestamp.'&Version='.VERSION;

	$request = "GET\necs.amazonaws.com\n/onca/xml\n".$requestparms;

	$request = str_replace(':', '%3A', $request);
	$request = str_replace(';', '%3B', $request);
	$request = str_replace(',', '%2C', $request);

	// Signing
	$signature = base64_encode(hash_hmac("sha256", $request, SECRET_ACCESS_KEY, TRUE));

	$signature = str_replace('+', '%2B', $signature);
	$signature = str_replace('=', '%3D', $signature);
	$signature = str_replace('/', '%2F', $signature);

	$fullrequest = 'http://ecs.amazonaws.com/onca/xml?'.$requestparms.'&Signature='.$signature;

	$xml = GetData($fullrequest, 10);
	$Result = XmlParser($xml);
}


/**
 *
 */
function TheHeader() {
	global $itemsincart;
	global $status_message;

	echo '<div style="text-align: center;"><h2><a href="'.$_SERVER["PHP_SELF"].'">Lou Donaldson CDs</a></h2></div><div style="text-align: right;">'.$status_message.'<a href="'.$_SERVER["PHP_SELF"].'?showcart='.$itemsincart.'"><img title="show cart or checkout" border="0" src="cart.jpg" /></a> '.$itemsincart.' items</div>';
}


/**
 *
 */
function TheProducts() {
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
		if ($item['OfferSummary']['TotalNew'] == '0' || $item['Offers']['TotalOffers'] == '0') {
			$link='Sorry, all sold out of new copies';
		} else {
			$link='<a href="'.$_SERVER["PHP_SELF"].'?add='.urlencode($item['Offers']['Offer'][0]['OfferListing'][0]['OfferListingId']).'">add to cart</a>';
		}

		echo '<td><div style="text-align: center;">'.$title.'</div><div style="text-align: center;">'.$image.'</div><div style="text-align: center;">'.$link.'</div></td>';
		$rowcount++;
	}
	echo '</tr></table';
	return;
}


/**
 *
 */
function TheCart() {
	global $Result;
	global $rtype;

//print_r($Result); die();
	$rtype = (key($Result) == 'CartGetResponse') ? 'CartGetResponse' : 'CartModifyResponse';
	echo '<table cellspacing="2" cellpadding="2">';
	echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post" name="lou" id="lou">';
	echo '<tr><td colspan="3"><h3>Your Shopping Cart</h3></td></tr>';
	echo '<tr><td colspan="3"><h4>Items In Your Cart:</h4></td></tr>';

	if (isset($Result[$rtype]['Cart'][0]['CartItems']['CartItem'][0])) {
		foreach ($Result[$rtype]['Cart'][0]['CartItems']['CartItem'] as $item) {

			$price = $item['Price']['FormattedPrice'];
			$itemtotal = $item['ItemTotal']['FormattedPrice'];
			$quantity = $item['Quantity'];
			$title = $item['Title'];

			echo '<tr>';
			echo '<td><div style="text-align: left;">Quantity: <input type="text" name="item_c_'.$quantity.'_'.$item['CartItemId'].'" size="4" maxlength="3" value="'.$quantity.'" /></div></td>';
			echo '<td><div style="text-align: left;">Title: '.$title.'</div></td>';
			echo '<td><div style="text-align: left;">Price: '.$price.'</div></td>';
			echo '<td><div style="text-align: left;">Item Total: '.$itemtotal.'</div></td>';
			echo '</tr>';
		}
	} else {
		echo '<tr>';
		echo '<td><div style="text-align: left;">No regular items are in your cart</div></td>';
		echo '</tr>';
	}

	echo '<tr><td colspan="3"><br /><h4>Saved For Later Items:</h4></td></tr>';

	if (isset($Result[$rtype]['Cart'][0]['SavedForLaterItems']['SavedForLaterItem'][0])) {
		foreach ($Result[$rtype]['Cart'][0]['SavedForLaterItems']['SavedForLaterItem'] as $item) {
			$price = $item['Price']['FormattedPrice'];
			$itemtotal = $item['ItemTotal']['FormattedPrice'];
			$quantity = $item['Quantity'];
			$title = $item['Title'];

			echo '<tr>';
			echo '<td><div style="text-align: left;">Quantity: <input type="text" name="item_s_'.$quantity.'_'.$item['CartItemId'].'" size="4" maxlength="3" value="'.$quantity.'" /></div></td>';
			echo '<td><div style="text-align: left;">Title: '.$title.'</div></td>';
			echo '<td><div style="text-align: left;">Price: '.$price.'</div></td>';
			echo '<td><div style="text-align: left;">Item Total: '.$itemtotal.'</div></td>';
			echo '</tr>';
		}
	} else {
		echo '<tr>';
		echo '<td><div style="text-align: left;">No Saved For Later items are in your cart</div></td>';
		echo '</tr>';
	}

	$totalprice= (isset($Result[$rtype]['Cart']['SubTotal']['FormattedPrice'])) ? $Result[$rtype]['Cart']['SubTotal']['FormattedPrice'] : '$0.00' ;
	echo '<tr>';
	echo '<td><br /><div style="text-align: left;">Grand Total: '.$totalprice.'</div></td>';
	echo '</tr>';

	$purchaseurl= (isset($Result[$rtype]['Cart']['PurchaseURL'])) ? $Result[$rtype]['Cart']['PurchaseURL'] : '' ;
	echo '<tr><td colspan="3"><br /><input type="hidden" name="modify" value="1" /><input type="submit" value="Update Cart" name="updatecart" />&nbsp;(Enter "0" to remove an item, "C" to move to cart, "S" to move to save for later)</td></tr><tr><td><input type="submit" value="Checkout Now At Amazon" name="checkout"/><input type="hidden" name="purchaseurl" value="'.$purchaseurl.'"</td></tr>';
	echo '</form>';
	echo '</table';
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
    <td width="100%" height="75"><?php TheHeader(); ?> </td>
  </tr>
   <tr>
   <td width="100%" height="250"><?php if ($showcart) TheCart(); else  TheProducts(); ?> </td>
  </tr>
  </table>
</body>
</html>
