<?php 

// jap tooltips won't show up right unless you select the right font
// Windows Control-Panel > Display Properties > Appearance > Advanced > Item: ToolTip)
// Won't work right if english words are used for Japanese search because I don't
// use lowercase/uppercase search for Japanese site
//
// Sometimes matches (esp german and french sites) same words without accents.
// I get the matches but I don't display the song titles because the words don't match.

//  http://sourceforge.net/projects/gettext   for Win32
//  You have to restart apache when you change the MO files because of shared caching?
//  You need to enter the stuff in UTF-8 into poedit

define('LOCALE_FR', 'fr_FR');
define('LOCALE_UK', 'en_GB');
define('LOCALE_DE', 'de_DE');
define('LOCALE_US', 'en_US');
define('LOCALE_JP', 'ja_JP');
define('MSG_DOMAIN', 'songsearch');

define('DEFAULT_LOCALE', LOCALE_US);
define('VERSION', '2011-08-01');

$ACCESS_KEY_ID = "XXXXXXXXXXXXXXXXXX";
$SECRET_ACCESS_KEY = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";


if (isset($_GET['locale'])) {
    //locale change requested
    $locale=$_GET['locale'];
    $oneday=time()+60*60*24*30; // Cookie lasts 30 days
    if (!setcookie('songsearch', $locale, $oneday)) {
        echo 'Your browser will not accept cookies';
    }
} elseif (isset($_COOKIE['songsearch'])) {
    $locale = stripslashes($_COOKIE['songsearch']);
} else {
    //No cookie. Create one
    $locale=DEFAULT_LOCALE;
    $oneday=time()+60*60*24*30;
    if (!setcookie('songsearch', $locale, $oneday)) {
        echo 'Your browser will not accept cookies';
    }
}
require_once("tools.inc.php");

// Set MSG_DOMAIN info for gettext
bindtextdomain(MSG_DOMAIN, './locale');
textdomain(MSG_DOMAIN);

// Language-specific defines
switch ($locale) {
    case LOCALE_DE:
    define ('TO_ENCODING', 'ISO-8859-1');
    putenv("LANG=".LOCALE_DE);
    setlocale(LC_ALL, LOCALE_DE);
    bind_textdomain_codeset(MSG_DOMAIN, TO_ENCODING);
    define ('DOMAIN', 'de');
    define ('NOIMAGE', 'http://images-eu.amazon.com/images/G/03/general/no-img-de_90x90.gif');
    break;
    case LOCALE_JP:
    define ('TO_ENCODING', 'Shift_JIS');
    putenv("LANG=".LOCALE_JP);
    setlocale(LC_ALL, LOCALE_JP);
    bind_textdomain_codeset(MSG_DOMAIN, TO_ENCODING);
    define ('DOMAIN', 'jp');
    define ('NOIMAGE', 'http://images-jp.amazon.com/images/G/09/x-locale/detail/thumb-no-image.gif');
    break;
    case LOCALE_UK:
    define ('TO_ENCODING', 'ISO-8859-1');
    putenv("LANG=".LOCALE_UK);
    setlocale(LC_ALL, LOCALE_UK);
    bind_textdomain_codeset(MSG_DOMAIN, TO_ENCODING);
    define ('DOMAIN', 'co.uk');
    define ('NOIMAGE', 'http://images-eu.amazon.com/images/G/02/misc/no-img-lg-uk.gif');
    break;
    case LOCALE_FR:
    default:
    define ('TO_ENCODING', 'ISO-8859-1');
    putenv("LANG=".LOCALE_FR);
    setlocale(LC_ALL, LOCALE_FR);
    bind_textdomain_codeset(MSG_DOMAIN, TO_ENCODING);
    define ('DOMAIN', 'fr');
    define ('NOIMAGE', 'http://images-eu.amazon.com/images/G/08/x-site/icons/no-img-lg.gif');
    break;
    case LOCALE_US:
    default:
    define ('TO_ENCODING', 'ISO-8859-1');
    putenv("LANG=".LOCALE_US);
    setlocale(LC_ALL, LOCALE_US);
    bind_textdomain_codeset(MSG_DOMAIN, TO_ENCODING);
    putenv("LANG=".LOCALE_US);
    setlocale(LC_ALL, LOCALE_US);
    bind_textdomain_codeset(MSG_DOMAIN, TO_ENCODING);
    define ('DOMAIN', 'com');
    define ('NOIMAGE', 'http://g-images.amazon.com/images/G/01/x-site/icons/no-img-lg.gif');
    define ('SUBMIT', 'Submit');
    break;
}

define('ASSOCIATES_ID','webservices-20');

if (!empty($_POST['songtitle'])) {

    $search_string=urlencode(langreverse(trim($_POST['songtitle'], ' ')));

$timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z");
$timestamp = str_replace(':','%3A',$timestamp);

$requestparms='AWSAccessKeyId='.$ACCESS_KEY_ID.'&AssociateTag='.ASSOCIATES_ID.'&Keywords='.$search_string.'&Operation=ItemSearch&ResponseGroup=Request,Small,Tracks,Images&SearchIndex=MusicTracks&Service=AWSECommerceService&Timestamp='.$timestamp.'&Version='.VERSION;

$request = "GET\necs.amazonaws.".DOMAIN."\n/onca/xml\n".$requestparms;

$request = str_replace(':','%3A',$request);
$request = str_replace(';','%3B',$request);
$request = str_replace(',','%2C',$request);

// Signing
$signature = base64_encode(hash_hmac("sha256",$request,$SECRET_ACCESS_KEY,TRUE));

$signature = str_replace('+','%2B',$signature);
$signature = str_replace('=','%3D',$signature);
$signature = str_replace('/','%2F',$signature);

$fullrequest = 'http://ecs.amazonaws.'.DOMAIN.'/onca/xml?'.$requestparms.'&Signature='.$signature;

    $xml = GetData($fullrequest, 10);
    $Result = xmlparser($xml);

} else {

    $Result='';
}

require_once('layout.php');

function findstring($haystack, $needle) {
    global $locale;

    switch ($locale) {
        case LOCALE_JP:
        return iconv_strpos($haystack, $needle, 0, 'UTF-8');
        // return mb_strpos($haystack, $needle, 0, 'UTF-8');
        break;
        case LOCALE_DE:
        case LOCALE_FR:
        $haystack=strtolower(utf8_decode($haystack));
        $needle=strtolower(utf8_decode($needle));
        return iconv_strpos(utf8_encode($haystack), utf8_encode($needle), 0, 'UTF-8');
        // $haystack=mb_strtolower($haystack, 'UTF-8');
        // $needle=mb_strtolower($needle, 'UTF-8');
        // return mb_strpos($haystack, $needle, 0, 'UTF-8');
        break;
        case LOCALE_US:
        case LOCALE_UK:
        default:
        return strpos(strtolower($haystack), strtolower($needle));

    }
    return true;
}

function lang($string) {
    global $locale;

    switch ($locale) {
        case LOCALE_JP:
        return iconv('UTF-8', TO_ENCODING, $string);
        // return mb_convert_encoding($string, TO_ENCODING, 'UTF-8');
        break;
        default:
        return utf8_decode($string);
    }
}

function langreverse($string) {

    // should be going from native encoding to UTF-8, but amazon is
    // currently encoding REST requests in ISO-8859, except Japan, which is in UTF-8

    global $locale;
    switch ($locale) {
        case LOCALE_JP:
        // return mb_convert_encoding($string, 'UTF-8', TO_ENCODING);
        return iconv(TO_ENCODING, 'UTF-8', $string);
        break;
        default:
        // return utf8_encode($string); 
        return $string;
    }
}


function theResults() {
    global $Result;

    if (empty($Result)) {
        echo gettext('Please enter keywords');
    } elseif  (isset($Result['ItemSearchResponse']['Items'][0]['Request']['Errors']['Error'][0]['Message']) and ($Result['ItemSearchResponse']['Items'][0]['TotalResults'] == '0'))  {
        echo lang($Result['ItemSearchResponse']['Items'][0]['Request']['Errors']['Error'][0]['Message']);
    } else {

        $keywords=$Result['ItemSearchResponse']['Items'][0]['Request']['ItemSearchRequest']['Keywords'];
        $words = explode(' ', $keywords);

        echo '<table width="100%" border="1"><tr>';
        foreach ($Result['ItemSearchResponse']['Items'][0]['Item'] as $item) {

            $trackout='';
            if (isset($item['Tracks']['Disc'])) {
                foreach ($item['Tracks']['Disc'] as $disc) {
                    $trackout .= gettext('Disc')." ".$disc['Number']."\n";
                    foreach ($disc['Track'] as $track) {
                        foreach ($words as $word) {
                            if (findstring($track['Track'], $word) === false) {
                                $trackout .= "";
                            } else {
                                $trackout .= "   ".gettext('Track')." ".$track['Number'].' - '.lang($track['Track'])."\n";
                            }
                        }
                    }
                }
                $trackout=str_replace('"', "&#34;", $trackout);
            } else {
                $trackout=gettext('No matching song titles were found');
            }


            if (isset($item['SmallImage']['URL'])) {
                $image='<img src="'.$item["SmallImage"]["URL"].'" title="'.$trackout.'" />';
            } else {
                $image='<img src="'.NOIMAGE.'" title="'.$trackout.'" />';
            }

            if (!isset($item["ItemAttributes"]["Title"])) continue;

            $title = '<a href="'.$item["DetailPageURL"].'" target="_blank">'.lang($item["ItemAttributes"]["Title"]).'</a>';
            echo '<td><div style="text-align: center;">'.$title.'</div><div style="text-align: center;">'.$image.'</div></td>';
        }
        echo '</tr></table>';

    }

}

?>
