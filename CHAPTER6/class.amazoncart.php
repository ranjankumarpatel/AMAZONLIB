<?php

error_reporting(E_ALL);

class AmazonCookieCart {
	var $Request = null;
	var $Status_Message = '';
	var $cartinfo = array();
	var $cartsize = 0;

	/**
	 *
	 *
	 * @param unknown $cookie (optional)
	 */


	function AmazonCookieCart($cookie = null) {
		if (!is_null($cookie)) {
			$this->cartinfo = unserialize(stripslashes(($cookie)));
			$this->cartsize = $this->cartinfo[0];
		}
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	function GetStatusMessage() {
		return $this->Status_Message;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	function GetCartSize() {
		return $this->cartsize;
	}


	/**
	 *
	 */
	function KillCookie() {
		setcookie(COOKIE_NAME, '', time()-10000);
	}


	/**
	 *
	 *
	 * @param unknown $cartarray
	 * @return unknown
	 */
	function SetCartCookie($cartarray) {
		$cart_encoded=serialize($cartarray);
		$cookie_expiration=time()+60*60*24*COOKIE_EXPIRATION;
		setcookie(COOKIE_NAME, $cart_encoded, $cookie_expiration);
		$this->cartsize = $cartarray[0];
		$this->cartinfo = $cartarray;
		return true;
	}





	/**
	 *
	 *
	 * @param unknown $reqparms
	 * @return unknown
	 */
	function PrepRequest($reqparms) {
		$timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z");
		$timestamp = str_replace(':', '%3A', $timestamp);

		$reqparms .= '&Timestamp='.$timestamp.'&Version='.VERSION;
		$request = "GET\necs.amazonaws.com\n/onca/xml\n".$reqparms;
		$request = str_replace(':', '%3A', $request);
		$request = str_replace(';', '%3B', $request);
		$request = str_replace(',', '%2C', $request);
		$signature = base64_encode(hash_hmac("sha256", $request, SECRET_ACCESS_KEY, TRUE));
		$signature = str_replace('+', '%2B', $signature);
		$signature = str_replace('=', '%3D', $signature);
		$signature = str_replace('/', '%2F', $signature);

		return 'http://ecs.amazonaws.com/onca/xml?'.$reqparms.'&Signature='.$signature;

	}


	/**
	 *
	 *
	 * @return unknown
	 */
	function GetDisplayCartRequest() {

		/*  if ($this->cartsize == "0") {
            $this->Status_Message='The cart is currently empty';
            return false;
        }  */

		$requestparms = 'AWSAccessKeyId='.ACCESS_KEY_ID.'&AssociateTag='.ASSOCIATES_ID.'&CartId='.$this->cartinfo[1].'&HMAC='.urlencode($this->cartinfo[2]).'&Operation=CartGet&ResponseGroup=Cart&Service=AWSECommerceService';
		$this->Request= $this->PrepRequest($requestparms);

		return true;
	}


	/**
	 *
	 *
	 * @param unknown $offerlistid
	 * @return unknown
	 */
	function GetAddCartRequest($offerlistid) {

		$requestparms = 'AWSAccessKeyId='.ACCESS_KEY_ID.'&AssociateTag='.ASSOCIATES_ID.'&CartId='.$this->cartinfo[1].'&HMAC='.urlencode($this->cartinfo[2]).'&Item.1.OfferListingId='.urlencode($offerlistid).'&Item.1.Quantity=1&Operation=CartAdd&ResponseGroup=Cart&Service=AWSECommerceService';
		$this->Request= $this->PrepRequest($requestparms);

		return true;
	}


	/**
	 *
	 *
	 * @param unknown $offerlistid
	 * @return unknown
	 */
	function GetCreateCartRequest($offerlistid) {


		$requestparms = 'AWSAccessKeyId='.ACCESS_KEY_ID.'&AssociateTag='.ASSOCIATES_ID.'&Item.1.OfferListingId='.urlencode($offerlistid).'&Item.1.Quantity=1&Operation=CartCreate&ResponseGroup=Cart&Service=AWSECommerceService';
		$this->Request= $this->PrepRequest($requestparms);

		return true;
	}


	/**
	 *
	 *
	 * @param unknown $postarray
	 * @return unknown
	 */
	function GetModifyCartRequest($postarray) {

		$suffix='';
		$count=0;
		foreach ( $postarray as $name => $change ) {
			$change=strtolower(trim($change));
			$pieces=explode('_', $name);
			if ($pieces[0] == 'item') {
				if (is_numeric($change)) {
					if ($change != $pieces[2]) {
						$count++;
						$suffix .= '&Item.'.$count.'.CartItemId='.$pieces[3].'&Item.'.$count.'.Quantity='.$change;
					}
				} elseif ($change == 'c' or $change == 's') {
					if ($pieces[1] != $change) {
						$count++;
						$action = ($change == 'c') ? 'MoveToCart' : 'SaveForLater';
						$suffix .= '&Item.'.$count.'.Action='.$action.'&Item.'.$count.'.CartItemId='.$pieces[3];
					}
				} else {
					$this->Status_Message='I did not understand this change request: '.$change;
				}
			}
		}

		if ($suffix != '') {
			$requestparms = 'AWSAccessKeyId='.ACCESS_KEY_ID.'&AssociateTag='.ASSOCIATES_ID.'&CartId='.$this->cartinfo[1].'&HMAC='.urlencode($this->cartinfo[2]).$suffix.'&Operation=CartModify&ResponseGroup=Cart&Service=AWSECommerceService';
			$this->Request= $this->PrepRequest($requestparms);
			/* echo $this->Request; die(); */
		} else {
			$this->Status_Message= (empty($this->Status_Message)) ? 'You did not request any changes to the cart' : $this->Status_Message;
			return false;
		}

		return true;
	}


	/**
	 *
	 *
	 * @param unknown $task
	 * @param unknown $cartvals (optional)
	 * @return unknown
	 */
	function ProcessCartRequest($task, $cartvals = null) {
		require_once 'tools.inc.php';

		switch ($task) {
		case 'create':
			$this->GetCreateCartRequest($cartvals);
			$xml = GetData($this->Request, 10);
			$p='/<(CartId|HMAC)*>(.*?)<\/\1>/';
			$matches=array();
			preg_match_all($p, $xml, $matches);
			$cartid=$matches[2][0];
			$hmac=$matches[2][1];
			$cart = array(1, $cartid, $hmac);
			$this->SetCartCookie($cart);
			$this->Status_Message='Item saved to your shopping cart';
			$Result = true; // Initial cartsize

			break;
		case 'add':
			$this->GetAddCartRequest($cartvals);

			$xml=GetData($this->Request, 10);

			$p='/<(Code)*>(.*?)<\/\1>/';
			$matches=array();
			preg_match_all($p, $xml, $matches);
			if ((isset($matches[2][0])) and ($matches[2][0] == 'AWS.ECommerceService.ItemAlreadyInCart')) {
				$this->Status_Message='That item is already in your shopping cart';
				$Result = true;
				break;
			}
			$this->cartinfo[0]++;
			$this->SetCartCookie($this->cartinfo);
			$this->Status_Message='Item saved to your shopping cart';
			$Result = true;

			break;
		case 'show':
			$this->GetDisplayCartRequest($cartvals);
			if (is_null($this->Request)) {
				$Result = false;
				break;
			}

			$xml = GetData($this->Request, 10);
			$Result = XmlParser($xml);
			$this->Status_Message='';
			break;

		case 'modify':
			$rc=$this->GetModifyCartRequest($cartvals);
			if (!$rc) {
				$Result=false;
				break;
			}
			$xml = GetData($this->Request, 10);
			$Result = XmlParser($xml);
			// Get cart size
			$this->cartsize = 0;
			if (isset($Result['CartModifyResponse']['Cart'][0]['CartItems']['CartItem'][0])) {
				foreach ($Result['CartModifyResponse']['Cart'][0]['CartItems']['CartItem'] as $item) {
					$this->cartsize += $item['Quantity'];
				}
			}
			if (isset($Result['CartModifyResponse']['Cart'][0]['SavedForLaterItems']['SavedForLaterItem'][0])) {
				foreach ($Result['CartModifyResponse']['Cart'][0]['SavedForLaterItems']['SavedForLaterItem'] as $item) {
					$this->cartsize += $item['Quantity'];
				}
			}
			$cart = array($this->cartsize, $this->cartinfo[1], $this->cartinfo[2]);
			$this->SetCartCookie($cart);

			break;
		}


		return $Result;
	}


}


?>
