<?php

$ACCESS_KEY_ID = "XXXXXXXXXXXXXXXXXX";
$SECRET_ACCESS_KEY = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

$timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z");
$timestamp = str_replace(':','%3A',$timestamp);

$requestparms ='AWSAccessKeyId='.$ACCESS_KEY_ID.'&AssociateTag=ws&ItemId=4860348206&Operation=ItemLookup&ResponseGroup=Small&Service=AWSECommerceService&Timestamp='.$timestamp.'&Version=2011-08-01';

$request = "GET\necs.amazonaws.jp\n/onca/xml\n".$requestparms;

$request = str_replace(':','%3A',$request);
$request = str_replace(';','%3B',$request);
$request = str_replace(',','%2C',$request);

// Signing
$signature = base64_encode(hash_hmac("sha256",$request,$SECRET_ACCESS_KEY,TRUE));

$signature = str_replace('+','%2B',$signature);
$signature = str_replace('=','%3D',$signature);
$signature = str_replace('/','%2F',$signature);

$fullrequest = 'http://ecs.amazonaws.jp/onca/xml?'.$requestparms.'&Signature='.$signature;

$xml=file_get_contents($fullrequest);

$p='@<Author>(.+)</Author>@';
preg_match ($p, $xml, $matches);

echo '<html><head><META HTTP-EQUIV="Content-Type" content="text/html;  charset=SHIFT-JIS"></head><body>';
echo 'The UTF-8 encoded Japanese character for "author" is '.$matches[1]. ' <br />';
echo 'The Shift_JIS encoded Japanese character for "author" is '.mb_convert_encoding($matches[1],"Shift_JIS","UTF-8");
echo '   </body></html>';

?>
